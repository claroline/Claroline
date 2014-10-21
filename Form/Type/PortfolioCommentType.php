<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\FormType
 */
class PortfolioCommentType extends AbstractType
{
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.context")
     * })
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->securityContext->getToken()->getUser();
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
