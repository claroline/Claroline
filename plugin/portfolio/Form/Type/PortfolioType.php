<?php

namespace Icap\PortfolioBundle\Form\Type;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commentsViewAt', 'datetime', [
                    'widget' => 'single_text',
                ])
            ->add('comments', TextType::class, ['mapped' => false])
            ->add('portfolioWidgets', TextType::class, ['mapped' => false])
            ->add('title', TextType::class);
    }

    public function getName()
    {
        return 'icap_portfolio_portfolio_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Icap\PortfolioBundle\Entity\Portfolio',
                'translation_domain' => 'icap_portfolio',
                'csrf_protection' => false,
                'date_format' => DateTimeType::HTML5_FORMAT,
            ]
        );
    }
}
