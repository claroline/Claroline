<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap_blog.form.widget_list")
 * @DI\FormType(alias = "blog_widget_list_form")
 */
class WidgetListType extends AbstractType
{
    //Voir controleur pour passer en partametre les options, 3eme param pour utiliser translator

    protected $translator;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //On appelle la méthode trans du service translator qu'on applique sur les deux variables
        //Ecrasement de $display--Label par son String traduit
        $displayBlockLabel = $this->translator->trans('display_block', [], 'widget');
        $displayInlineLabel = $this->translator->trans('display_inline', [], 'widget');

        $builder
            ->add('widgetListBlogs', 'collection', array(
                'type' => 'blog_widget_list_blog_form',
                'by_reference' => false,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('widgetDisplayListBlogs', 'choice', array(
                'choices' => array(
                    'b' => $displayBlockLabel,
                    'l' => $displayInlineLabel,
                ),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'empty_value' => false,
            ));
    }

    public function getName()
    {
        return 'blog_widget_list_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BlogBundle\Entity\WidgetBlogList',
                'translation_domain' => 'icap_blog',
            )
        );
    }
}
