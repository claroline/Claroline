<?php

/*
 * This file is part of the EoHoneypotBundle package.
 *
 * (c) Eymen Gunay <eymen@egunay.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.form.honeypot")
 * @DI\FormType(alias = "honeypot")
 */
class HoneypotType extends AbstractType
{
    /**
     * @var Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!$data) {
                return;
            }

            $form->getParent()->addError(new FormError($this->translator->trans('field_not_empty', [], 'platform')));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required' => false,
            'mapped' => false,
            'data' => '',
            'label' => ' ',
            'attr' => [
                'autocomplete' => 'off',
                'tabindex' => -1,
                'aria-label' => $this->translator->trans('leave_empty', [], 'platform'),
                // Fake `display:none` css behaviour to hide input
                // as some bots may also check inputs visibility
                'style' => 'position: fixed; left: -100%; top: -100%;',
            ],
        ]);
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'honeypot';
    }
}
