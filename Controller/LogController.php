<?php

namespace ArturDoruch\EventLoggerBundle\Controller;

use ArturDoruch\EventLoggerBundle\Form\LogFilterType;
use ArturDoruch\EventLoggerBundle\Log\Driver\Exception\LogNotFoundException;
use ArturDoruch\EventLoggerBundle\Log\Driver\LogDriverInterface;
use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection;
use ArturDoruch\EventLoggerBundle\LogStates;
use ArturDoruch\EventLoggerBundle\Templating\CssClassHelper;
use ArturDoruch\ListBundle\ItemList;
use ArturDoruch\ListBundle\Paginator;
use ArturDoruch\ListBundle\Request\QueryParameterBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogController extends AbstractController
{
    /**
     * @var LogDriverInterface
     */
    private $logDriver;

    /**
     * @var LogPropertyCollection
     */
    private $logPropertyCollection;

    /**
     * @var array
     */
    private $logCategories;

    public function __construct(LogDriverInterface $logDriver, LogPropertyCollection $logPropertyCollection)
    {
        $this->logDriver = $logDriver;
        $this->logPropertyCollection = $logPropertyCollection;
        $this->logCategories = $logPropertyCollection->get('category')->getFilterFormChoices();
    }


    public function purgeList()
    {
        $categories = [];

        foreach ($this->logCategories as $category) {
            $categories[] = [
                'name' => $category,
                'totalLogs' => $this->logDriver->count($category)
            ];
        }

        return $this->render('@ArturDoruchEventLogger/log/purge_list.html.twig', ['categories' => $categories]);
    }


    public function purge($token, Request $request)
    {
        if (!$this->isCsrfTokenValid('purge', $token)) {
            return new Response('Invalid CSRF Token.', 404);
        }

        $purge = array_keys($request->request->get('purge', []));
        $category = array_shift($purge);

        if (!in_array($category, $this->logCategories)) {
            $this->addFlash('error', sprintf('Not recognized the <b>%s</b> category.', $category));
        } else {
            try {
                $removed = $this->logDriver->purge($category);

                $this->addFlash('success', sprintf('Purged %d logs from the <b>%s</b> category.', $removed, $category));
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('arturdoruch_eventlogger_log_purgelist');
    }

    /**
     * @todo Display filter form errors when request is type of XHR.
     */
    public function list(Request $request, FormFactoryInterface $formFactory)
    {
        $form = $formFactory->createNamed('filter', LogFilterType::class);
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
        $pagination->setItemLimits($this->container->getParameter('arturdoruch_eventlogger.log.list_item_limits'));

        foreach ($pagination->getItems() as &$log) {
            $log = $this->logDriver->prepare($log);
        }

        //$this->logPropertyCollection->get('category')->listable(!$form->get('category')->getData());

        return $this->render('@ArturDoruchEventLogger/log/list.html.twig', [
            'logList' => new ItemList($pagination, $form),
            'logStates' => LogStates::all(),
            'cssClassHelper' => new CssClassHelper(),
            'showCategory' => !$form->get('category')->getData(),
            'listableProperties' => $this->logPropertyCollection->getListable()
        ]);
    }


    public function show($id, Request $request)
    {
        try {
            $log = $this->logDriver->get($id);
        } catch (LogNotFoundException $e) {
            if ($request->isXmlHttpRequest()) {
                return new Response($e->getMessage(), 404);
            }

            throw $this->createNotFoundException($e->getMessage());
        }

        $templateParameters = [
            'log' => $log,
            'logStates' => LogStates::all(),
            'cssClassHelper' => new CssClassHelper(),
            'logPropertyCollection' => $this->get('arturdoruch_eventlogger.log_property_collection')
        ];

        try {
            return new Response($this->renderView("@ArturDoruchEventLogger/log/log.html.twig", $templateParameters));
        } catch (\Throwable $e) {
            if ($request->isXmlHttpRequest()) {
                return new Response($this->createErrorMessage($e, 'Log template rendering'), 500);
            }

            throw $e;
        }
    }


    public function remove($id, $token, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $this->doRemove($id, $token);

                return new Response(sprintf('Log with id <b>%s</b> has been removed.', $id));
            } catch (\Throwable $e) {
                return new Response($this->createErrorMessage($e, 'Log removing'), 400);
            }
        }

        $this->doRemove($id, $token);

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }

    /**
     * @param string $id
     * @param string $token
     */
    private function doRemove($id, $token)
    {
        if (!$this->isCsrfTokenValid('remove', $token)) {
            throw new \InvalidArgumentException('Invalid CSRF Token.');
        }

        $this->logDriver->remove([$id]);
    }


    public function removeMany(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $data = $this->getRequestData($request, false, true);
                $this->logDriver->remove($data['ids']);

                return new Response();
            } catch (\Throwable $e) {
                return new Response($this->createErrorMessage($e, 'Logs removing'), 400);
            }
        }

        $data = $this->getRequestData($request, false);
        $this->logDriver->remove($ids = $data['ids'] ?? []);

        $this->addFlash('success', sprintf('Removed <b>%d</b> logs.', count($ids)));

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }

    /**
     * Changes state of the log.
     */
    public function changeState($id, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $data = $this->getRequestData($request, true);
                $log = $this->logDriver->changeState($data['state'], $id);

                return new JsonResponse($this->logPropertyCollection->logToArray($log));
            } catch (\Throwable $e) {
                return new Response($this->createErrorMessage($e, 'Log state changing'), 400);
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
     * condition="request.headers.get('X-Requested-With') == 'XMLHttpRequest'"
     */
    public function changeStateMany(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            try {
                $data = $this->getRequestData($request, true, true);
                $result = $this->changeLogsState($data['state'], $data['ids']);

                return new JsonResponse($result);
            } catch (\Throwable $e) {
                return new Response($this->createErrorMessage($e, 'Logs state changing'), 400);
            }
        }

        $data = $this->getRequestData($request, true);
        $this->changeLogsState($data['state'], $ids = $data['ids'] ?? []);

        $this->addFlash('success', sprintf('Changed state to <b>%s</b> of the <b>%d</b> logs.', $data['state'], count($ids)));

        return $this->redirectToRoute('arturdoruch_eventlogger_log_list');
    }

    /**
     * @param int $state
     * @param array $ids The log ids.
     *
     * @return array
     */
    private function changeLogsState($state, array $ids): array
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


    private function createErrorMessage(\Throwable $e, string $action): string
    {
        return $action . ' error' . ($this->container->getParameter('kernel.environment') === 'prod' ? '.' :  ': ' . $e->getMessage());
    }
}
