<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\Angular\AngularType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

//FORM TODO
class HomeTabType extends AngularType
{
    private $color;
    private $forApi = false;
    private $locked;
    private $ngAlias;
    private $type;
    private $visible;
    private $workspace;

    public function __construct($type = 'desktop', $color = null, $locked = false, $visible = true, Workspace $workspace = null, $ngAlias = 'htfmc')
    {
        $this->color = $color;
        $this->locked = $locked;
        $this->ngAlias = $ngAlias;
        $this->type = $type;
        $this->visible = $visible;
        $this->workspace = $workspace;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, ['constraints' => new NotBlank(), 'label' => 'name']);
        $builder->add(
            'color',
            TextType::class,
            [
                'required' => false,
                'mapped' => false,
                'label' => 'color',
                'data' => $this->color,
                'attr' => ['colorpicker' => 'hex'],
            ]
        );

        if ('admin' === $this->type) {
            $builder->add(
                'visible',
                ChoiceType::class,
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'visible',
                    'required' => true,
                    'mapped' => false,
                    // *this line is important*
                    'choices_as_values' => true,
                    'data' => $this->visible,
                ]
            );
            $builder->add(
                'locked',
                ChoiceType::class,
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'locked',
                    'mapped' => false,
                    'required' => true,
                    'choices_as_values' => true,
                    'data' => $this->locked,
                ]
            );
            $builder->add(
                'roles',
                'entity',
                [
                    'label' => 'roles',
                    'class' => 'ClarolineCoreBundle:Role',
                    'choice_translation_domain' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->where('r.workspace IS NULL')
                            ->andWhere('r.type = 1')
                            ->andWhere('r.name != :anonymousRole')
                            ->setParameter('anonymousRole', 'ROLE_ANONYMOUS')
                            ->orderBy('r.translationKey', 'ASC');
                    },
                    'property' => 'translationKey',
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                ]
            );
        } elseif ('workspace' === $this->type && !is_null($this->workspace)) {
            $builder->add(
                'visible',
                ChoiceType::class,
                [
                    'choices' => [
                        'yes' => true,
                        'no' => false,
                    ],
                    'label' => 'visible',
                    'required' => true,
                    'mapped' => false,
                    // *this line is important*
                    'choices_as_values' => true,
                    'data' => $this->visible,
                ]
            );
            $workspace = $this->workspace;
            $builder->add(
                'roles',
                'entity',
                [
                    'label' => 'roles',
                    'class' => 'ClarolineCoreBundle:Role',
                    'choice_translation_domain' => true,
                    'query_builder' => function (EntityRepository $er) use ($workspace) {
                        return $er->createQueryBuilder('r')
                            ->where('r.workspace = :workspace')
                            ->orWhere('r.name = :userRoleName')
                            ->setParameter('workspace', $workspace)
                            ->setParameter('userRoleName', 'ROLE_USER')
                            ->orderBy('r.translationKey', 'ASC');
                    },
                    'property' => 'translationKey',
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
                ]
            );
        }
    }

    public function getName()
    {
        return 'home_tab_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $default = ['translation_domain' => 'platform'];

        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $default['ng-model'] = 'homeTab';
        $default['ng-controllerAs'] = $this->ngAlias;

        $resolver->setDefaults($default);
    }

    public function enableApi()
    {
        $this->forApi = true;
    }
}
