<?php

namespace Icap\LessonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap.lesson.chaptertype")
 */
class ChapterType extends AbstractType
{
    protected $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('text', 'tinymce', array(
                    'attr' => array(
                        'data-theme' => 'advanced',
                        'height' => '600',
                    ),
                )
            )
        ;
        if ($options['chapters'] != null) {
            $root = true;
            $parentId = null;
            foreach ($options['chapters'] as $child) {
                if ($root) {
                    $choices[$child->getId()] = $this->translator->trans('Root', array(), 'icap_lesson');
                    $root = false;
                } else {
                    $choices[$child->getId()] = $child->getTitle();
                }
                //check that the provided parentId is a legit chapter id
                if ($options['parentId'] == $child->getId()) {
                    $parentId = $child->getId();
                }
            }
            $builder->add('parentChapter', 'choice', array(
                'mapped' => false,
                'choices' => $choices,
                'data' => $parentId,
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\LessonBundle\Entity\Chapter',
            'chapters' => array(),
            'parentId' => null,
            'no_captcha' => true,
        ));
    }

    public function getName()
    {
        return 'icap_lesson_chaptertype';
    }
}
