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

class CoursesWidgetConfigurationType extends AbstractType
{
    private $user;
    private $extra;
    private $translator;
    private $isAdmin;

    public function __construct(User $user, TranslatorInterface $translator, $extra = [], $isAdmin = false)
    {
        $this->user = $user;
        $this->translator = $translator;
        $this->extra = $extra;
        $this->isAdmin = $isAdmin;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;

        if ($this->isAdmin) {
            $builder->add(
                'displayAll',
                'checkbox',
                [
                    'mapped' => false,
                    'data' => isset($this->extra['displayAll']) ? $this->extra['displayAll'] : false,
                    'label' => 'display_all_courses',
                    'translation_domain' => 'cursus',
                ]
            );
        }
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
                ],
                'label' => 'default_mode',
            ]
        );
        $builder->add(
            'publicSessionsOnly',
            'checkbox',
            ['label' => 'public_sessions_only']
        );
        $builder->add(
            'collapseCourses',
            'checkbox',
            [
                'mapped' => false,
                'data' => isset($this->extra['collapseCourses']) ? $this->extra['collapseCourses'] : false,
                'label' => 'collapse_courses',
                'translation_domain' => 'cursus',
            ]
        );
        $builder->add(
            'collapseSessions',
            'checkbox',
            [
                'mapped' => false,
                'data' => isset($this->extra['collapseSessions']) ? $this->extra['collapseSessions'] : false,
                'label' => 'collapse_sessions',
                'translation_domain' => 'cursus',
            ]
        );
    }

    public function getName()
    {
        return 'courses_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
