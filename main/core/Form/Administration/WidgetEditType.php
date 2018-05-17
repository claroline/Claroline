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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class WidgetEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'defaultWidth',
            IntegerType::class,
            [
                'label' => 'width',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 1, 'max' => 12]),
                ],
                'attr' => ['min' => 1, 'max' => 12],
            ]
        );
        $builder->add(
            'defaultHeight',
            IntegerType::class,
            [
                'label' => 'height',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 1]),
                ],
                'attr' => ['min' => 1],
            ]
        );
        $builder->add(
            'isDisplayableInDesktop',
            CheckboxType::class,
            ['label' => 'displayable_in_desktop', 'required' => true]
        );
        $builder->add(
            'isDisplayableInWorkspace',
            CheckboxType::class,
            ['label' => 'displayable_in_workspace', 'required' => true]
        );
        $builder->add(
            'roles',
            'entity',
            [
                'label' => 'roles_for_desktop_widget',
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
            ]
        );
    }

    public function getName()
    {
        return 'widget_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'platform']);
    }
}
