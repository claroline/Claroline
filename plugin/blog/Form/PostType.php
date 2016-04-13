<?php

namespace Icap\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("icap_blog.form.post")
 */
class PostType extends AbstractType
{
    /** @var \Claroline\CoreBundle\Manager\EventManager */
    private $authorizationChecker;

    /**
     * @DI\InjectParams({
     *     "authorizationChecker" = @DI\Inject("security.authorization_checker")
     * })
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                    'theme_options' => array('control_width' => 'col-md-12'),
                    'constraints' => new Assert\NotBlank(array(
                        'message' => 'blog_post_need_title',
                    )),
                )
            )
            ->add('content', 'tinymce', array(
                    'attr' => array(
                        'style' => 'height: 300px;',
                    ),
                    'theme_options' => array('control_width' => 'col-md-12'),
                    'constraints' => new Assert\NotBlank(array(
                        'message' => 'blog_post_need_content',
                    )),
                )
            )
            ->add('tags', 'tags')
        ;

        $authorizationChecker = $this->authorizationChecker;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($authorizationChecker, $options) {
                $form = $event->getForm();
                $data = $event->getData();
                $blog = $data->getBlog();

                if ($authorizationChecker->isGranted('EDIT', $blog) || $authorizationChecker->isGranted('POST', $blog)) {
                    $form->add('publicationDate', 'datepicker', array(
                            'required' => false,
                            'read_only' => true,
                            'component' => true,
                            'autoclose' => true,
                            'language' => $options['language'],
                            'format' => $options['date_format'],
                       )
                    );
                }
            }
        );
    }

    public function getName()
    {
        return 'icap_blog_post_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'icap_blog',
            'data_class' => 'Icap\BlogBundle\Entity\Post',
            'csrf_protection' => true,
            'intention' => 'create_post',
            'language' => 'en',
            'date_format' => DateType::HTML5_FORMAT,
        ));
    }
}
