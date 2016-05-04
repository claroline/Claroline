<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class DropzoneCommonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $defaultDateTimeOptions = array(
            'required' => false,
            'read_only' => false,
            'component' => true,
            'autoclose' => true,
            'language' => $options['language'],
            'date_format' => $options['date_format'],
            'format' => $options['date_format'],
        );

        $builder
            ->add('stayHere', 'hidden', array(
                'mapped' => false,
            ))
            ->add('autoCloseForManualStates', 'hidden', array(
                'mapped' => false,
            ))
            ->add('instruction', 'tinymce', array(
                'required' => false,
            ))

            ->add('allowWorkspaceResource', 'checkbox', array('required' => false))
            ->add('allowUpload', 'checkbox', array('required' => false))
            ->add('allowUrl', 'checkbox', array('required' => false))
            ->add('allowRichText', 'checkbox', array('required' => false))

            ->add('successMessage', 'tinymce', array('required' => false))
            ->add('failMessage', 'tinymce', array('required' => false))

            ->add('manualPlanning', 'choice', array(
                'required' => true,
                'choices' => array(
                    true => 'manualPlanning',
                    false => 'sheduleByDate',
                ),
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('manualState', 'choice', array(
                'choices' => array(
                    'allowDrop' => 'allowDropManualState',
                    'finished' => 'finishedManualState',
                ),
//                'data' => 'allowDrop',
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('startAllowDrop', 'datetimepicker', $defaultDateTimeOptions)
            ->add('endAllowDrop', 'datetimepicker', $defaultDateTimeOptions);

        $builder
            // Accusé de réception. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('published', 'checkbox',
                array(
                    'attr' => array(),
                    'mapped' => false,
                    'required' => false, )
                     );

        // $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
        //     $dropzone = $event->getData();
        //     $form = $event->getForm();
        //     $publishedAttr = array();

        //     // check if the Product object is "new"
        //     // If no data is passed to the form, the data is "null".
        //     // This should be considered a new "Product"
        //     if ($dropzone->getResourceNode()->isPublished()) {
        //         $publishedAttr['checked'] = 'checked';
        //     }

        //     $form
        //         // Publication. Ajout de cette zone, demande JJQ. InnovaERV
        //         ->add('published', 'checkbox',
        //             array(
        //                 'attr' => $publishedAttr,
        //                 'mapped' => false,
        //                 'required' => false)
        //                  );
        // });

        // $builder->addEventListener(
        // FormEvents::POST_SUBMIT,
        // function (FormEvent $event) {
        //     $dropzone = $event->getData();
        //     $form = $event->getForm();
        //     $publishedAttr = array();

        //     // check if the Product object is "new"
        //     // If no data is passed to the form, the data is "null".
        //     // This should be considered a new "Product"
        //     if ($dropzone->getResourceNode()->isPublished()) {
        //         $publishedAttr['checked'] = 'checked';
        //     }

        //     $form
        //         // Publication. Ajout de cette zone, demande JJQ. InnovaERV
        //         ->add('published', 'checkbox',
        //             array(
        //                 'attr' => $publishedAttr,
        //                 'mapped' => false,
        //                 'required' => false)
        //                  );
        //     }
        // );

        $builder
            // Accusé de réception. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('returnReceipt', 'checkbox',
                 array(
//                     'attr' => array('checked' => 'checked'),
                     'required' => false, )
                      )
            // Evaluation. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('evaluation', 'checkbox',
                 array(
                     'required' => false, )
                      )
            // Picture. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('picture', 'checkbox',
                 array(
                     'required' => false, )
                      )
            // Username. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('username', 'checkbox',
                 array(
                     'required' => false, )
                      )

            // EvaluationType. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('evaluationType', 'choice', array(
                'choices' => array(
                    'noEvaluation' => 'noEvaluation',
                    'notation' => 'notation',
                    'ratingScale' => 'ratingScale',
                ),
                'expanded' => false,
                'multiple' => false,
            ))

            // Notation maxi. Ajout de cette zone, demande JJQ. InnovaERV
            ->add('maximumNotation', 'integer', array(
                          'required' => true, 'attr' => array('min' => 0), )
                        )

            ;
    }
    public function getName()
    {
        return 'innova_collecticiel_common_form';
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'language' => 'fr',
                'translation_domain' => 'innova_collecticiel',
                'date_format' => DateType::HTML5_FORMAT,
            )
        );
    }
}
