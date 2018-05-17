<?php

namespace FormaLibre\SupportBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminTicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'contactMail',
            EmailType::class,
            [
                'required' => true,
                'label' => 'contact_email',
            ]
        );
        $builder->add(
            'contactPhone',
            TextType::class,
            [
                'required' => true,
                'label' => 'contact_phone',
            ]
        );
        $builder->add(
            'title',
            TextType::class,
            [
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'description',
            'tinymce',
            [
                'required' => true,
                'label' => 'description',
                'translation_domain' => 'platform',
            ]
        );
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
                        ->where('t.locked = true')
                        ->orderBy('t.name', 'ASC');
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]
        );
    }

    public function getName()
    {
        return 'admin_ticket_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'support']);
    }
}
