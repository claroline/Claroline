<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class WidgetEditType extends AbstractType
{
    private $isDisplayableInDesktop;

    public function __construct($isDisplayableInDesktop)
    {
        $this->isDisplayableInDesktop = $isDisplayableInDesktop;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'defaultWidth',
            'integer',
            array(
                'label' => 'width',
                'required' => true,
                'constraints' => array(
                    new NotBlank(),
                    new Range(array('min' => 1, 'max' => 12)),
                ),
                'attr' => array('min' => 1, 'max' => 12),
            )
        );
        $builder->add(
            'defaultHeight',
            'integer',
            array(
                'label' => 'height',
                'required' => true,
                'constraints' => array(
                    new NotBlank(),
                    new Range(array('min' => 1)),
                ),
                'attr' => array('min' => 1),
            )
        );

        if ($this->isDisplayableInDesktop) {
            $builder->add(
                'roles',
                'entity',
                array(
                    'label' => 'roles',
                    'class' => 'ClarolineCoreBundle:Role',
                    'choice_translation_domain' => true,
                    'query_builder' => function (EntityRepository $er) {
                        $queryBuilder = $er->createQueryBuilder('r')
                            ->andWhere('r.type = :roleType')
                            ->setParameter('roleType', Role::PLATFORM_ROLE);
                        $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->eq('r.name', '?1')))
                            ->setParameter(1, 'ROLE_ANONYMOUS');

                        return $queryBuilder;
                    },
                    'property' => 'translationKey',
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                )
            );
        }
    }

    public function getName()
    {
        return 'widget_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
