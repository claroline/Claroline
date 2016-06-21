<?php

namespace Icap\WikiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DeleteSectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data->hasChildren()) {
                    $form->add('children', 'checkbox', array(
                        'required' => false,
                        'mapped' => false,
                    ));
                } else {
                    $form->add('children', 'hidden', array(
                        'required' => false,
                        'mapped' => false,
                    ));
                }
            }
        );
    }

    public function getName()
    {
        return 'icap_wiki_delete_section_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_wiki',
            'data_class' => 'Icap\WikiBundle\Entity\Section',
        ));
    }
}
