<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nicolas
 * Date: 17/10/13
 * Time: 09:57
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\LessonBundle\Form;

use Doctrine\ORM\EntityManager;
use Icap\LessonBundle\Entity\Chapter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @DI\Service("icap.lesson.duplicatechaptertype")
 */
class DuplicateChapterType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    protected $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(EntityManager $entityManager, $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder) {
                $form = $event->getForm();
                $data = $event->getData();

                $chapters = $this->entityManager->getRepository('IcapLessonBundle:Chapter')->getChapterAndChapterChildren($data->getLesson()->getRoot());
                $chapters_list = array();
                $root = true;

                foreach ($chapters as $child) {
                    if ($root) {
                        $chapters_list[$child->getId()] = $this->translator->trans('Root', array(), 'icap_lesson');
                        $root = false;
                    } else {
                        $chapters_list[$child->getId()] = $child->getTitle();
                    }
                }

                $form
                    ->add('title', 'text', array(
                            'mapped' => false,
                            'data' => $this->translator->trans('copy_prefix', array(), 'icap_lesson').$data->getTitle(),
                        )
                    )
                    ->add('parent', 'choice',
                        array(
                            'mapped' => false,
                            'choices' => $chapters_list,
                        )
                    );

                if ($this->entityManager->getRepository('IcapLessonBundle:Chapter')->childCount($data) > 0) {
                    $form
                        ->add('duplicate_children', 'checkbox',
                            array(
                                'mapped' => false,
                                'required' => false,
                            )
                    );
                }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\LessonBundle\Entity\Chapter',
            'no_captcha' => true,
        ));
    }

    public function getName()
    {
        return 'icap_lesson_duplicatechaptertype';
    }
}
