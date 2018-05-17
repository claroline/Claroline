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

use Claroline\AgendaBundle\Entity\EventInvitation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class EventInvitationType extends AbstractType
{
    private $trans;

    public function __construct(TranslatorInterface $trans)
    {
        $this->trans = $trans;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'form.title',
                'required' => true,
            ])

            ->add('description', 'tinymce', [
                'label' => 'form.description',
            ])

            ->add('status', ChoiceType::class, [
                'choices' => [
                    EventInvitation::JOIN => $this->trans->trans('invitation_join_label', [], 'agenda'),
                    EventInvitation::MAYBE => $this->trans->trans('invitation_maybe_label', [], 'agenda'),
                ],
                'label' => 'form.status',
            ])
        ;
    }

    public function getName()
    {
        return 'agenda_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'class' => 'Claroline\AgendaBundle\Entity\Event',
                'translation_domain' => 'agenda',
            )
        );
    }
}
