<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if ($data === null || $data->getSection() === null || $data->getSection()->isRoot() === false) {
                    $form->add('title', 'text', array(
                            'theme_options' => array('label_width' => 'col-md-1', 'control_width' => 'col-md-11'),
                        )
                    );
                }
                $form->add('text', 'tinymce', array(
                    'theme_options' => array('label_width' => 'col-md-1', 'control_width' => 'col-md-11'),
                    'attr' => array(
                          'id' => 'icap_wiki_section_text',
                        ),
                    )
                );
            }
        );
    }

    public function getName()
    {
        return 'icap_wiki_contribution_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Contribution',
        ));
    }
}
