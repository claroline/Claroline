<?php

namespace Icap\BibliographyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BookReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label' => 'name',
                'constraints' => new Assert\NotBlank(),
                'attr' => [
                    'autofocus' => true,
                    'data-ng-model' => 'vm.bookReference.title',
                ],
            ]
        );

        $builder->add(
            'isbn',
            'text',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.isbn13',
                ],
                'label' => 'isbn',
                'constraints' => [
                    new Assert\Length(['min' => 10, 'max' => 14]),
                    new Assert\NotBlank(),
                ],
            ]
        );

        $builder->add(
            'author',
            'text',
            [
                'required' => true,
                'label' => 'author',
                'constraints' => new Assert\NotBlank(),
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.author_data[0].name',
                ],
            ]
        );

        $builder->add(
            'description',
            'textarea',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.summary',
                    'class' => 'form-control',
                ],
                'label' => 'description',
            ]
        );

        $builder->add(
            'abstract',
            'textarea',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.abstract',
                    'class' => 'form-control',
                ],
                'label' => 'abstract',
            ]
        );

        $builder->add(
            'publisher',
            'text',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.publisher_text',
                ],
                'label' => 'publisher',
            ]
        );

        $builder->add(
            'printer',
            'text',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.printer',
                ],
                'label' => 'printer',
            ]
        );

        $builder->add(
            'publicationYear',
            'integer',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.publicationYear',
                ],
                'label' => 'publication_year',
                'constraints' => new Assert\Range(['min' => 0]),
            ]
        );

        $builder->add(
            'language',
            'language',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.language',
                ],
                'label' => 'language',
            ]
        );

        $builder->add(
            'pageCount',
            'integer',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.pageCount',
                ],
                'label' => 'page_count',
                'constraints' => new Assert\Range(['min' => 1]),
            ]
        );

        $builder->add(
            'url',
            'url',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.url',
                ],
                'label' => 'url',
                'constraints' => new Assert\Url(['checkDNS' => true]),
            ]
        );

        $builder->add(
            'coverUrl',
            'url',
            [
                'required' => false,
                'attr' => [
                    'data-ng-model' => 'vm.bookReference.coverUrl',
                ],
                'label' => 'cover_url',
                'constraints' => new Assert\Url(['checkDNS' => true]),
            ]
        );
    }

    public function getName()
    {
        return 'icap_bibliography_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'icap_bibliography',
            'data_class' => 'Icap\BibliographyBundle\Entity\BookReference',
            'csrf_protection' => true,
            'intention' => 'create_book_reference',
        ]);
    }
}
