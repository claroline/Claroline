<?php

namespace Icap\BlogBundle\Form;

use Icap\BlogBundle\Form\DataTransformer\IntToBlogTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_blog.form.widget_list_blog")
 * @DI\FormType(alias = "blog_widget_list_blog_form")
 */
class WidgetListBlogType extends AbstractType
{
    /**
     * @var IntToBlogTransformer
     */
    private $intToBlogTransformer;

    /**
     * @DI\InjectParams({
     *     "intToBlogTransformer" = @DI\Inject("icap_blog.transformer.int_to_blog")
     * })
     */
    public function __construct(IntToBlogTransformer $intToBlogTransformer)
    {
        $this->intToBlogTransformer = $intToBlogTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('resourceNode', 'resourcePicker', array(
            'theme_options' => array(
                'label_width' => 'col-md-6',
                'control_width' => 'col-md-6',
            ),
            'attr' => array(
                'data-is-picker-multi-select-allowed' => 0,
                'data-is-directory-selection-allowed' => 0,
                'data-type-white-list' => 'icap_blog',
            ),
            'display_browse_button' => false,
            'display_download_button' => false,
        ));
    }

    public function getName()
    {
        return 'blog_widget_list_blog_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Icap\BlogBundle\Entity\WidgetListBlog',
                'translation_domain' => 'icap_blog',
            )
        );
    }
}
