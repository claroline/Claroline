<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CoursesWidgetConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'cursus',
            'entity',
            array(
                'class' => 'ClarolineCursusBundle:Cursus',
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('c')
                        ->where('c.course IS NULL')
                        ->orderBy('c.title', 'ASC');
                },
                'property' => 'titleAndCode',
                'required' => false,
                'label' => 'cursus',
            )
        );
    }

    public function getName()
    {
        return 'courses_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'cursus'));
    }
}
