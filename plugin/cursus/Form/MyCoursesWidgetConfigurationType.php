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

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\CoursesWidgetConfig;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MyCoursesWidgetConfigurationType extends AbstractType
{
    private $user;
    private $extra;
    private $translator;

    public function __construct(User $user, TranslatorInterface $translator, $extra = [])
    {
        $this->user = $user;
        $this->translator = $translator;
        $this->extra = $extra;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;
        $builder->add(
            'cursus',
            'entity',
            [
                'class' => 'ClarolineCursusBundle:Cursus',
                'query_builder' => function (EntityRepository $er) use ($user) {
                    if ($user->hasRole('ROLE_ADMIN')) {
                        return $er->createQueryBuilder('c')
                            ->where('c.course IS NULL')
                            ->orderBy('c.title', 'ASC');
                    } else {
                        $organizations = $user->getOrganizations();

                        return $er->createQueryBuilder('c')
                            ->join('c.organizations', 'o')
                            ->where('c.course IS NULL')
                            ->andWhere('o IN (:organizations)')
                            ->setParameter('organizations', $organizations)
                            ->orderBy('c.title', 'ASC');
                    }
                },
                'property' => 'titleAndCode',
                'required' => false,
                'label' => 'cursus',
            ]
        );
        $builder->add(
            'defaultMode',
            'choice',
            [
                'multiple' => false,
                'choices' => [
                    CoursesWidgetConfig::MODE_LIST => $this->translator->trans('list_view', [], 'cursus'),
                    CoursesWidgetConfig::MODE_CALENDAR => $this->translator->trans('calendar_view', [], 'cursus'),
                    CoursesWidgetConfig::MODE_CHRONOLOGIC => $this->translator->trans('chronologic_view', [], 'cursus'),
                ],
                'label' => 'default_mode',
            ]
        );
        $builder->add(
            'openSessionsColor',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'data' => isset($this->extra['openSessionsColor']) ? $this->extra['openSessionsColor'] : null,
                'label' => 'color',
                'translation_domain' => 'platform',
                'attr' => ['class' => 'cursus_colorpicker'],
            ]
        );
        $builder->add(
            'closedSessionsColor',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'data' => isset($this->extra['closedSessionsColor']) ? $this->extra['closedSessionsColor'] : null,
                'label' => 'color',
                'translation_domain' => 'platform',
                'attr' => ['class' => 'cursus_colorpicker'],
            ]
        );
        $builder->add(
            'unstartedSessionsColor',
            'text',
            [
                'required' => false,
                'mapped' => false,
                'data' => isset($this->extra['unstartedSessionsColor']) ? $this->extra['unstartedSessionsColor'] : null,
                'label' => 'color',
                'translation_domain' => 'platform',
                'attr' => ['class' => 'cursus_colorpicker'],
            ]
        );
        $builder->add(
            'displayClosedSessions',
            'checkbox',
            [
                'mapped' => false,
                'data' => isset($this->extra['displayClosedSessions']) ? $this->extra['displayClosedSessions'] : true,
                'label' => 'display',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'displayUnstartedSessions',
            'checkbox',
            [
                'mapped' => false,
                'data' => isset($this->extra['displayUnstartedSessions']) ? $this->extra['displayUnstartedSessions'] : true,
                'label' => 'display',
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'disableClosedSessionsWs',
            'checkbox',
            [
                'mapped' => false,
                'data' => isset($this->extra['disableClosedSessionsWs']) ? $this->extra['disableClosedSessionsWs'] : false,
                'label' => 'disable_workspace_link',
            ]
        );
        $builder->add(
            'disableUnstartedSessionsWs',
            'checkbox',
            [
                'mapped' => false,
                'data' => isset($this->extra['disableUnstartedSessionsWs']) ? $this->extra['disableUnstartedSessionsWs'] : false,
                'label' => 'disable_workspace_link',
            ]
        );
    }

    public function getName()
    {
        return 'my_courses_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
