<?php

namespace FormaLibre\SupportBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class TicketType extends AbstractType
{
    private $mode;
    private $translator;

    public function __construct(TranslatorInterface $translator, $mode = 0)
    {
        $this->mode = $mode;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            'text',
            [
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform',
            ]
        );

        if ($this->mode === 1) {
            $builder->add(
                'description',
                'textarea',
                [
                    'required' => false,
                    'label' => 'additional_infos',
                    'translation_domain' => 'support',
                    'attr' => ['placeholder' => $this->translator->trans('description_placeholder_text', [], 'support')],
                ]
            );
        } else {
            $builder->add(
                'description',
                'tinymce',
                [
                    'required' => true,
                    'label' => 'description',
                    'translation_domain' => 'platform',
                ]
            );
        }

        if ($this->mode === 0) {
            $builder->add(
                'type',
                'entity',
                [
                    'label' => 'type',
                    'class' => 'FormaLibreSupportBundle:Type',
                    'translation_domain' => 'support',
                    'choice_translation_domain' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->orderBy('t.name', 'ASC');
                    },
                    'property' => 'formName',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                ]
            );
        }
        $builder->add(
            'contactMail',
            'email',
            [
                'required' => true,
                'label' => 'contact_email',
            ]
        );
        $builder->add(
            'contactPhone',
            'text',
            [
                'required' => true,
                'label' => 'contact_phone',
            ]
        );
    }

    public function getName()
    {
        return 'ticket_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'support']);
    }
}
