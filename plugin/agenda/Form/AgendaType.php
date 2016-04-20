<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Form;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.agenda")
 */
class AgendaType extends AbstractType
{
    private $translator;
    private $om;
    private $tokenStorage;
    private $isDesktop;
    private $guestMode = false;

    /**
     * @DI\InjectParams({
     *     "translator"   = @DI\Inject("translator"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(TranslatorInterface $translator, ObjectManager $om, TokenStorageInterface $tokenStorage)
    {
        $this->translator = $translator;
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->isDesktop = false;
    }

    public function setIsDesktop()
    {
        $this->isDesktop = true;
    }

    public function setGuestMode()
    {
        $this->guestMode = true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', [
                'label' => 'form.title',
                'required' => true,
            ])
        ;

        if (!$this->guestMode) {
            $builder
                ->add('isTask', 'checkbox', [
                    'label' => 'form.task',
                    'required' => false,
                ])

                ->add('isAllDay', 'checkbox', [
                    'label' => 'form.all_day',
                    'required' => false,
                ])

                ->add('start', 'text', [
                    'label' => 'form.start',
                ])

                ->add('end', 'text', [
                    'label' => 'form.end',
                ])
            ;
        }

        if ($this->isDesktop && !$this->guestMode) {
            $builder->add('workspace', 'entity', [
                'label' => $this->translator->trans('workspace', [], 'platform'),
                'class' => 'Claroline\CoreBundle\Entity\Workspace\Workspace',
                'required' => false,
                'choices' => $this->getWorkspacesByUser(),
                'empty_value' => $this->translator->trans('desktop', [], 'platform'),
                'property' => 'name',
            ]);
        }

        $builder
            ->add('description', 'tinymce', [
                'label' => 'form.description',
            ])
        ;

        if (!$this->guestMode) {
            $builder
                ->add('priority', 'choice', [
                    'label' => 'form.priority',
                    'choices' => [
                        '#FF0000' => 'high',
                        '#01A9DB' => 'medium',
                        '#848484' => 'low',
                    ],
                ])

                ->add('users', 'userpicker', [
                    'label' => 'form.invitations',
                    'translation_domain' => 'agenda',
                    'multiple' => true,
                    'mapped' => false,
                ])
            ;
        }
    }

    public function getWorkspacesByUser()
    {
        return $this->om->getRepository('ClarolineAgendaBundle:Event')->findEditableUserWorkspaces($this->tokenStorage->getToken()->getUser());
    }

    public function getName()
    {
        return 'agenda_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'workspace' => new Workspace(),
                'user' => new User(),
                'class' => 'Claroline\AgendaBundle\Entity\Event',
                'translation_domain' => 'agenda',
            )
        );
    }
}
