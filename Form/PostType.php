<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_blog.form.post")
 */
class PostType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Manager\EventManager */
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
        $builder
            ->add('title', 'text', array(
                    'theme_options' => array('control_width' => 'col-md-12'),
                    'constraints' => new Assert\NotBlank(array(
                        'message' => 'blog_post_need_title'
                    ))
                )
            )
            ->add('content', 'tinymce', array(
                    'attr' => array(
                        'style' => 'height: 300px;'
                    ),
                    'theme_options' => array('control_width' => 'col-md-12'),
                    'constraints' => new Assert\NotBlank(array(
                        'message' => 'blog_post_need_content'
                    ))
                )
            )
            ->add('tags', 'tags')
        ;

        if ($this->securityContext->isGranted('POST', $this->securityContext->getToken()->getUser())) {
            $builder->add('publicationDate', 'datepicker', array(
                    'required'      => false,
                    'read_only'     => true,
                    'component'     => true,
                    'autoclose'     => true,
                    'language'      => $options['language'],
                    'format'        => $options['date_format']
               )
            );
        }
    }

    public function getName()
    {
        return 'icap_blog_post_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_blog',
            'data_class'      => 'Icap\BlogBundle\Entity\Post',
            'csrf_protection' => true,
            'intention'       => 'create_post',
            'language'        => 'en',
            'date_format'     => DateType::HTML5_FORMAT
        ));
    }
}
