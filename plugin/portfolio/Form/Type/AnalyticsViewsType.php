<?php

namespace Icap\PortfolioBundle\Form\Type;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class AnalyticsViewsType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $withPortfolioSelect;

    /**
     * @param TranslatorInterface $translator
     * @param User                $user
     * @param bool|false          $withPortfolioSelect
     */
    public function __construct(TranslatorInterface $translator, User $user, $withPortfolioSelect = false)
    {
        $this->translator = $translator;
        $this->user = $user;
        $this->withPortfolioSelect = $withPortfolioSelect;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'range', 'daterange', array(
                    'label' => $this->translator->trans('period', [], 'icap_portfolio').' :',
                    'required' => true,
                    'attr' => array('class' => 'input-sm'),
                    'theme_options' => array('label_width' => 'col-md-2', 'control_width' => 'col-md-3'),
                )
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if ($this->withPortfolioSelect) {
                $form = $event->getForm();
                $user = $this->user;

                $form
                    ->add(
                        'portfolio', 'entity', array(
                            'class' => 'IcapPortfolioBundle:Portfolio',
                            'property' => 'title',
                            'query_builder' => function (EntityRepository $entityRepository) use ($user) {
                                return $entityRepository->createQueryBuilder('p')
                                    ->where('p.user = :user')
                                    ->setParameter('user', $user);
                            },
                            'label' => 'show',
                            'attr' => array('class' => 'input-sm'),
                            'theme_options' => array('label_width' => 'col-md-2', 'control_width' => 'col-md-3'),
                        )
                    );
            }
        });
    }

    public function getName()
    {
        return 'icap_portfolio_analytics_views';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
            )
        );
    }
}
