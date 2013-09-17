<?php

/**
 * ExoOnLine
 * Copyright or © or Copr. Université Jean Monnet (France), 2012
 * dsi.dev@univ-st-etienne.fr
 *
 * This software is a computer program whose purpose is to [describe
 * functionalities and technical features of your software].
 *
 * This software is governed by the CeCILL license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
*/

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExerciseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name', 'hidden', array(
                    'data' => 'exercise'
                )
            )
            ->add(
                'title', 'text', array(
                    'label' => 'title'
                )
            )
            ->add(
                'description', 'textarea', array(
                    'label' => 'Description',
                    'attr' => array('class' => 'tinymce', 'data-theme' => 'medium'),
                    'required' => false
                )
            )
            ->add(
                'shuffle', 'checkbox', array(
                    'required' => false, 'label' => 'Exercise.shuffle'
                )
            )
            ->add(
                'nbQuestion', 'text', array(
                    'label' => 'number of questions to draw'
                )
            )
            //->add('dateCreate')
            ->add(
                'duration', 'text', array(
                    'label' => 'Exercise.duration'
                )
            )
            //->add('nbQuestionPage')
            ->add(
                'doprint', 'checkbox', array(
                    'required' => false, 'label' => 'print paper'
                )
            )
            ->add(
                'maxAttempts', 'text', array(
                    'label' => 'maximum number of tries'
                )
            )
            //->add('correctionMode', 'text', array('label' => 'Availability of correction'))
            ->add(
                'correctionMode', 'choice', array(
                    'label' => 'availability of correction',
                    'choices' => array(
                        '1' => 'At the end of assessment',
                        '2' => 'After the last attempt',
                        '3' => 'From',
                        '4' => 'Never'
                    )
                )
            )
            ->add(
                'dateCorrection', 'datetime', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy hh:mm:ss',
                    'attr' => array('data-format' => 'dd/MM/yyyy hh:mm:ss'),
                    'label' => 'correction date',
                    'read_only' => true
                )
            )
            ->add(
                'markMode', 'choice', array(
                    'label' => 'availability of score',
                    'choices' => array(
                        '1' => 'At the same time that the correction',
                        '2' => 'At the end of assessment'
                    )
                )
            )
            ->add(
                'start_date', 'datetime', array(
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy hh:mm:ss',
                'attr' => array('data-format' => 'dd/MM/yyyy hh:mm:ss'),
                'label' => 'start date',
                'read_only' => true
                )
            )
            ->add(
                'useDateEnd', 'checkbox', array(
                    'required' => false, 'label' => 'use date of end'
                )
            )
            ->add(
                'end_date', 'datetime', array(
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'format' => 'dd/MM/yyyy hh:mm:ss',
                    'attr' => array('data-format' => 'dd/MM/yyyy hh:mm:ss'),
                    'label' => 'Exercise.end_date',
                    'read_only' => true
                )
            )
            ->add(
                'dispButtonInterrupt', 'checkbox', array(
                    'required' => false, 'label' => 'test exit'
                )
            )
            ->add(
                'lockAttempt', 'checkbox', array(
                    'required' => false, 'label' => 'lock attempt'
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Exercise',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_exercisetype';
    }
}