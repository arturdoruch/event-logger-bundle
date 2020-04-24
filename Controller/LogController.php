<?php

namespace ArturDoruch\EventLoggerBundle\Controller;

use ArturDoruch\EventLoggerBundle\Form\LogFilterType;
use ArturDoruch\EventLoggerBundle\Log\Driver\LogDriverInterface;
use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection;
use ArturDoruch\EventLoggerBundle\LogStates;
use ArturDoruch\EventLoggerBundle\Templating\CssClassHelper;
use ArturDoruch\ListBundle\ItemList;
use ArturDoruch\ListBundle\Paginator;
use ArturDoruch\ListBundle\Request\QueryParameterBag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogController extends Controller
{
    /**
     * @var LogDriverInterface
     */
    private $logDriver;

    /**
     * @var array
     */
    private $logCategories;

    /**
     * @var LogPropertyCollection
     */
    private $logPropertyCollection;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->logDriver = $this->get('arturdoruch_eventlogger.log_driver');
        $this->logPropertyCollection = $this->get('arturdoruch_eventlogger.log_property_collection');
        $this->logCategories = $this->logPropertyCollection->get('category')->getFilterFormChoices();
    }

    /**
     * @Route(
     *     "/purge",
     *     methods={"GET"}
     * )
     * @Template("@ArturDoruchEventLogger/log/purge_list.html.twig")
     *
     * @return array
     */
    public function purgeListAction(Request $request)
    {
        $categories = [];

        foreach ($this->logCategories as $category) {
            $categories[] = [
                'name' => $category,
                'totalLogs' => $this->logDriver->count($category)
            ];
        }

        return ['categories' => $categories];
    }

    /**
     * @Route(
     *     "/purge/{token}",
     *     methods={"POST"}
     * )
     */
    public function purgeAction($token, Request $request)
    {
        if (!$this->isCsrfTokenValid('purge', $token)) {
            return new Response('Invalid CSRF Token.', 404);
        }

        $purge = array_keys($request->request->get('purge', []));
        $channel = array_shift($purge);

        if (!in_array($channel, $this->logCategories)) {
            $this->addFlash('error', sprintf('Not recognized log channel <b>%s</b>.', $channel));
        } else {
            try {
                $removed = $this->logDriver->purge($channel);

                $this->addFlash('success', sprintf('Purged %d logs from <b>%s</b> channel.', $removed, $channel));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('arturdoruch_eventlogger_log_purgelist');
    }

    /**
     * @todo Display filter form errors when request is type of XHR.
     *
     * @Route(
     *     "/",
     *     methods={"GET"}
     * )
     * @Template("@ArturDoruchEventLogger/log/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamed('filter', LogFilterType::class);
        $form->handleRequest($request);

        $parameterBag = new QueryParameterBag($request);
        $criteria = [];

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $criteria = $form->getData();
            } elseif ($request->isXmlHttpRequest()) {
                // todo Return and handle form errors by js.
                /*$formErrors = $this->getFormErrors($form);

                return new JsonResponse([
                    'type' => 'request_error',
                    'formErrors' => $formErrors
                ], 400);*/
            }
        }

        $query = $this->logDriver->getQuery($criteria, $parameterBag->getSort('createdAt', 'desc'));
        $pagination = Paginator::paginate($query, $parameterBag->getPage(), $parameterBag->getLimit(100));
        $pagination->setItemLimits($this->getParameter('arturdoruch_eventlogger.log.list_item_limits'));

        foreach ($pagination->getItems() as &$log) {
            $log = $this->logDriver->prepare($log);
        }

        //$this->logPropertyCollection->get('category')->listable(!$form->get('category')->getData());

        return [
            'logList' => new ItemList($pagination, $form),
            'logStates' => LogStates::all(),
            'cssClassHelper' => new CssClassHelper(),
            'showCategory' => !$form->get('category')->getData(),
            'listableProperties' => $this->logPropertyCollection->getListable()
        ];
    }

    /*
     * @param FormInterface $form
     * @return array
     */
    /*private function getFormErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && ($childErrors = $this->getFormErrors($childForm))) {
                $name = $form->getName() . '[' . $childForm->getName() . ']';
                $errors[$name] = $childErrors;
            }
        }

        return $errors;
    }*/

    /**
     * @Route(
     *      "/{id}",
     *      methods={"GET"}
     * )
     * @Template("@ArturDoruchEventLogger/log/log.html.twig")
     */
    public function showAction($id, Request $request)
    {
        try {
            $log = $this->logDriver->get($id);
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return new Response($e->getMessage(), 404);
            }

            throw $this->createNotFoundException($e->getMessage());
        }

        return [
            'log' => $log,
            'logStates' => LogStates::all(),
            'cssClassHelper' => new CssClassHelper(),
            'logPropertyCollection' => $this->get('arturdoruch_eventlogger.log_property_collection')
        ];
    }

    /**
     * @Route(
     *      "/remove/{id}/{token}",
     *      methods={"DELETE", "POST"}
     * )
     */
    public function removeAction($id, $token, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $this->removeLog($id, $token);

                return new Response(sprintf('Log with id <b>%s</b> has been removed.', $id));
            } catch (\Exception $e) {
                return new Response($e->getMessage(), 400);
            }
        }

        $this->removeLog($id, $token);

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }

    /**
     * @param string $id
     * @param string $token
     */
    private function removeLog($id, $token)
    {
        if (!$this->isCsrfTokenValid('remove', $token)) {
            throw new \InvalidArgumentException('Invalid CSRF Token.');
        }

        $this->logDriver->remove([$id]);
    }

    /**
     * @Route(
     *      "/remove",
     *      methods={"POST"},
     * )
     *
     * condition="request.headers.get('X-Requested-With') == 'XMLHttpRequest'"
     */
    public function removeManyAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $data = $this->getRequestData($request, false, true);
                $this->logDriver->remove($data['ids']);

                return new Response();
            } catch (\Exception $e) {
                return new Response($e->getMessage(), 400);
            }
        }

        $data = $this->getRequestData($request, false);
        $this->logDriver->remove($ids = $data['ids'] ?? []);

        $this->addFlash('success', sprintf('Removed <b>%d</b> logs.', count($ids)));

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }


    /**
     * Change state of the log.
     *
     * @Route(
     *      "/change-state/{id}",
     *      methods={"POST", "PATCH"},
     * )
     */
    public function changeStateAction($id, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $data = $this->getRequestData($request, true);
                $log = $this->logDriver->changeState($data['state'], $id);

                return new JsonResponse($this->logPropertyCollection->logToArray($log));
            } catch (\Exception $e) {
                return new Response($e->getMessage(), 400);
            }
        }

        $data = $this->getRequestData($request, true);
        $this->logDriver->changeState($data['state'], $id);

        if ($request->request->get('logView')) {
            return $this->redirectToRoute('arturdoruch_eventlogger_log_show', ['id' => $id]);
        }

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }

    /**
     * Changes state of many logs.
     *
     * @Route(
     *      "/change-state",
     *      methods={"POST"},
     * )
     *
     * condition="request.headers.get('X-Requested-With') == 'XMLHttpRequest'"
     */
    public function changeStateManyAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $data = $this->getRequestData($request, true, true);
                $result = $this->changeLogsState($data['state'], $data['ids']);

                return new JsonResponse($result);
            } catch (\Exception $e) {
                return new Response($e->getMessage(), 400);
            }
        }

        $data = $this->getRequestData($request, true);
        $this->changeLogsState($data['state'], $ids = $data['ids'] ?? []);

        $this->addFlash('success', sprintf(
            'Changed state to <b>%s</b> of the <b>%d</b> logs.', $data['state'], count($ids)
        ));

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }

    /**
     * @param array $ids The log ids.
     * @param int $state
     *
     * @return array
     */
    private function changeLogsState($state, array $ids)
    {
        $success = [];
        $failure = [];

        foreach ($ids as $id) {
            try {
                $log = $this->logDriver->changeState($state, $id);
                $success[] = $this->logPropertyCollection->logToArray($log);
            } catch (\Exception $e) {
                $failure[] = $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'failure' => $failure
        ];
    }


    private function getRequestData(Request $request, bool $expectedState = false, bool $expectedIds = false): array
    {
        if (null === $data = $request->request->all()) {
            throw new \InvalidArgumentException('Missing request parameters.');
        }

        if ($expectedState) {
            if (!isset($data['state'])) {
                throw new \InvalidArgumentException('Missing request "state" parameter.');
            }

            if (!in_array($state = (int) $data['state'], [0, 1, 2])) {
                throw new \InvalidArgumentException(sprintf('Invalid "state" parameter "%s". Permissible values are: 0, 1, 2.', $state));
            }

            $data['state'] = $state;
        }

        if ($expectedIds && !isset($data['ids'])) {
            throw new \InvalidArgumentException('Missing request "ids" parameter.');
        }

        return $data;
    }
}
