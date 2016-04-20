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

use Claroline\CoreBundle\Repository\WidgetRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class WidgetInstanceType extends AbstractType
{
    private $isDesktop;
    private $withRole;
    private $roles;

    public function __construct($bundles = array(), $isDesktop = true, $withRole = false, array $roles = array())
    {
        $this->isDesktop = $isDesktop;
        $this->withRole = $withRole;
        $this->roles = $roles;
        $this->bundles = $bundles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $datas['is_desktop'] = $this->isDesktop;
        $datas['with_role'] = $this->withRole;
        $datas['roles'] = $this->roles;
        $bundles = $this->bundles;

        $builder->add('name', 'text', array('constraints' => new NotBlank()));
        $builder->add(
            'widget',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\Widget\Widget',
                'choice_translation_domain' => true,
                'expanded' => false,
                'multiple' => false,
                'constraints' => new NotBlank(),
                'query_builder' => function (WidgetRepository $widgetRepo) use ($datas, $bundles) {
                    if ($datas['is_desktop']) {
                        if ($datas['with_role']) {
                            return $widgetRepo->createQueryBuilder('w')
                                ->join('w.roles', 'r')
                                ->where('w.isDisplayableInDesktop = true')
                                ->andWhere('r IN (:roles)')
                                ->leftJoin('w.plugin', 'p')
                                ->andWhere('CONCAT(p.vendorName, p.bundleName) IN (:bundles)')
                                ->orWhere('w.plugin is null')
                                ->setParameter('roles', $datas['roles'])
                                ->setParameter('bundles', $bundles);
                        } else {
                            return $widgetRepo->createQueryBuilder('w')
                                ->where('w.isDisplayableInDesktop = true');
                        }
                    } else {
                        return $widgetRepo->createQueryBuilder('w')
                            ->where('w.isDisplayableInWorkspace = true');
                    }
                },
            )
        );
    }

    public function getName()
    {
        return 'widget_instance_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'widget'));
    }
}
