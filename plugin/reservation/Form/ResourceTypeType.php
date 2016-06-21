<?php

namespace FormaLibre\ReservationBundle\Form;

use Symfony\Component\Form\AbstractType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("formalibre.form.resourceType")
 */
class ResourceTypeType extends AbstractType
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
                'label' => 'form.name',
            )
        );
    }

    public function getName()
    {
        return 'resource_type_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'FormaLibre\ReservationBundle\Entity\ResourceType',
                'translation_domain' => 'reservation',
            )
        );
    }
}
