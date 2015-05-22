<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\FormType
 */
class PortfolioCommentType extends AbstractType
{
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new \LogicException(
                'Unable to get connected user to create a comment on the portfolio!'
            );
        }

        $builder
            ->add('message', 'text')
            ->add('sender', 'text', array('mapped' => false))
            ->add('date', 'text', array('mapped' => false));

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($user) {
                /** @var \Icap\PortfolioBundle\Entity\PortfolioComment $data */
                $data = $event->getData();

                $data->setSender($user);
            }
        );
    }

    public function getName()
    {
        return 'icap_portfolio_portfolio_comment_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Icap\PortfolioBundle\Entity\PortfolioComment',
                'translation_domain' => 'icap_portfolio_comment',
                'csrf_protection'    => false,
            )
        );
    }
}
