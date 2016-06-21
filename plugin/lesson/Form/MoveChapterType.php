<?php

namespace Icap\LessonBundle\Form;

use Doctrine\ORM\EntityManager;
use Icap\LessonBundle\Entity\Chapter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @DI\Service("icap.lesson.movechaptertype")
 */
class MoveChapterType extends AbstractType
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder, $options) {
                $filter = $options['attr']['filter'];
                $form = $event->getForm();
                $data = $event->getData();

                if ($data != null) {
                    $chapterRepository = $this->entityManager->getRepository('IcapLessonBundle:Chapter');
                    $chapters = $chapterRepository->getChapterAndChapterChildren($data->getLesson()->getRoot());
                    $nonLegitTargets = $chapterRepository->getChapterAndChapterChildren($data);

                    //add current parent to non legit destination list
                    if ($data->getParent() != null) {
                        array_push($nonLegitTargets, $data->getParent());
                    }

                    $chapters_list = array();
                    foreach ($chapters as $child) {
                        //add and rename lesson root, if legit destination
                        if ($child->getId() == $child->getRoot() && ($filter == 0 || $this->isLegitTarget($child, $nonLegitTargets))) {
                            $chapters_list[$child->getId()] = $this->translator->trans('Root', array(), 'icap_lesson');
                        //add chapters as legit destination for move, after checking if legit
                        } elseif ($filter == 0 || $this->isLegitTarget($child, $nonLegitTargets)) {
                            $chapters_list[$child->getId()] = $child->getTitle();
                        }
                    }

                    $form
                        ->add('choiceChapter', 'choice', array(
                            'mapped' => false,
                            'choices' => $chapters_list,
                        ));
                }

                $form
                    ->add('brother', 'checkbox', array(
                        'required' => false,
                        'mapped' => false,
                    ))
                    ->add('firstposition', 'hidden', array(
                        'required' => false,
                        'mapped' => false,
                        'data' => 'false',
                    ));
        });
    }

    private function isLegitTarget($chapter, $list)
    {
        foreach ($list as $key2 => $chap2) {
            if ($chapter->getId() == $chap2->getId()) {
                return false;
            }
        }

        return true;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Icap\LessonBundle\Entity\Chapter',
        ));
    }

    public function getName()
    {
        return 'icap_lesson_movechaptertype';
    }

/*    public function isNotRoot(FormInterface $form)
    {

    }*/
}
