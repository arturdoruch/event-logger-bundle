<?php

namespace ArturDoruch\EventLoggerBundle\Form;

use ArturDoruch\EventLoggerBundle\Log\LogPropertyCollection;
use ArturDoruch\EventLoggerBundle\Log\Property\DateTimeProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class LogFilterType extends AbstractType
{
    public static $choicePlaceholder = '';

    /**
     * @var LogPropertyCollection
     */
    private $logPropertyCollection;

    public function __construct(LogPropertyCollection $logPropertyCollection)
    {
        $this->logPropertyCollection = $logPropertyCollection;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('csrf_protection', false);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        foreach ($this->logPropertyCollection->all() as $property) {
            if (!$property->isFilterable()) {
                continue;
            }

            $type = $property->getType();
            $name = $property->getName();

            if (null !== $choices = $property->getFilterFormChoices()) {
                $builder->add($name, Type\ChoiceType::class, [
                    'placeholder' => $property->isFilterFormChoicePlaceholderRequired() ? self::$choicePlaceholder : '',
                    'choices' => $choices,
                    'required' => false
                ]);
            } elseif ($property instanceof DateTimeProperty) {
                $this->addDateRangeChildren($builder, $name, $property->getFilterFormFormat());
            } else {
                $builder->add($name, Type\TextType::class, [
                    'required' => false
                ]);
            }
        }
    }


    private function addDateRangeChildren(FormBuilderInterface $builder, $name, $format)
    {
        static $positions = ['From', 'To'];

        foreach ($positions as $position) {
            $builder->add($name . $position, Type\DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'format' => $format,
                'attr' => [
                    'data-type' => 'date',
                    'data-format' => $format,
                    'placeholder' => $format
                ],
                /*'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Date([
                        'format' => $format
                    ])
                ]*/
            ]);
        }
    }
}
