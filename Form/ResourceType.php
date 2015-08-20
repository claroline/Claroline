<?php

namespace FormaLibre\ReservationBundle\Form;

use Symfony\Component\Form\AbstractType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("formalibre.form.resource")
 */
class ResourceType extends AbstractType
{
    private $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
                'label' => 'form.name'
            )
        );

        $builder->add('description', 'textarea', array(
            'label' => 'form.description'
        ));

        $builder->add('maxTimeReservation', 'time', array(
            'input' => 'string',
            'widget' => 'single_text',
            'label' => 'form.maxTime'
        ));
    }

    public function getName()
    {
        return 'resource_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'FormaLibre\ReservationBundle\Entity\Resource',
                'translation_domain' => 'reservation'
            )
        );
    }
}