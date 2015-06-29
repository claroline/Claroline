<?php

namespace Icap\PortfolioBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Icap\PortfolioBundle\Manager\WidgetTypeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\FormType
 */
class PortfolioWidgetType extends AbstractType
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var WidgetTypeManager
     */
    protected $widgetTypeManager;

    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "widgetTypeManager" = @DI\Inject("icap_portfolio.manager.widget_type")
     * })
     */
    public function __construct(TokenStorageInterface $tokenStorage, WidgetTypeManager $widgetTypeManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->widgetTypeManager = $widgetTypeManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            throw new \LogicException(
                'Unable to get token from security storage for portfolio widget form!'
            );
        }
        $user = $token->getUser();
        if (!$user) {
            throw new \LogicException(
                'Unable to get connected user for portfolio widget form!'
            );
        }

        $builder
            ->add('portfolio_id', 'entity', [
                'class' => 'IcapPortfolioBundle:Portfolio',
                'query_builder' => function(EntityRepository $entityRepository) use ($user) {
                    return $entityRepository->createQueryBuilder('p')
                        ->where('p.user = :user')
                        ->setParameter('user', $user)
                    ;
                },
                'property_path' => 'portfolio'
            ])
            ->add('widget_id', 'entity', [
                'class' => 'IcapPortfolioBundle:Widget\AbstractWidget',
                'query_builder' => function(EntityRepository $entityRepository) use ($user) {
                    return $entityRepository->createQueryBuilder('w')
                        ->where('w.user = :user')
                        ->setParameter('user', $user)
                    ;
                },
                'property_path' => 'widget'
            ])
            ->add('column', 'integer')
            ->add('row', 'integer')
            ->add('sizeX', 'integer')
            ->add('sizeY', 'integer')
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($user) {
                $form = $event->getForm();
                $choices = [];
                $choiceTypes = $this->widgetTypeManager->getWidgetsTypes();

                foreach ($choiceTypes as $choiceType) {
                    $choices[$choiceType['name']] = $choiceType['name'];
                }

                $form->add('widget_type', 'choice', [
                    'choices' => $choices
                ]);
            }
        );
    }

    public function getName()
    {
        return 'icap_portfolio_portfolio_widget_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\PortfolioBundle\Entity\PortfolioWidget',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection'=> false,
            ]
        );
    }
}
