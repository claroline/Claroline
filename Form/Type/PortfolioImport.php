<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @DI\FormType
 */
class PortfolioImport extends AbstractType
{
    protected $availableImportFormats = [];

    public function __construct(array $availableImportFormats)
    {
        $this->availableImportFormats = $availableImportFormats;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', 'file', [
            'theme_options' => ['control_width' => 'col-md-5']
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
            $product = $event->getData();
            $form = $event->getForm();

            $form->add('format', 'choice', [
                'choices'  => $this->availableImportFormats,
                'expanded' => true
            ]);
        });
    }

    public function getName()
    {
        return 'icap_portfolio_import_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\ImportData',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection'    => false,
                'date_format'        => DateTimeType::HTML5_FORMAT
            )
        );
    }
}
