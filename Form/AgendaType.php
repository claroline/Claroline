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
use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @DI\Service("claroline.form.agenda")
 */
class AgendaType extends AbstractType
{
    private $translator;
    private $editMode;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->editMode = false;
    }

    public function setEditMode()
    {
        $this->editMode = true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'label' => 'form.title',
                'required' => true
            ))

            ->add('isTask', 'checkbox', [
                'label' => 'form.task',
                'required' => false
            ])

            ->add('isAllDay', 'checkbox', [
                'label' => 'form.all_day',
                'required' => false
            ])

            ->add('start', 'text', [
                'label' => 'form.start'
            ])

            ->add('end', 'text', [
                'label' => 'form.end'
            ])

            ->add('description', 'tinymce', [
                'label' => 'form.description'
            ])

            ->add('priority', 'choice', [
                'label' => 'form.priority',
                'choices' => [
                    '#FF0000' => 'high',
                    '#01A9DB' => 'medium',
                    '#848484' => 'low'
                ]
            ]);
    }

    public function getName()
    {
        return 'agenda_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'workspace' => new Workspace() ,
                'user' => new User(),
                'class' => 'Claroline\AgendaBundle\Entity\Event',
                'translation_domain' => 'agenda'
            )
        );
    }
}
