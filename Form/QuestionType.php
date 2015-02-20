<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Claroline\CoreBundle\Entity\User;

use UJM\ExoBundle\Repository\CategoryRepository;

class QuestionType extends AbstractType
{

    private $user;
    private $catID;

    public function __construct(User $user, $catID = -1)
    {
        $this->user  = $user;
        $this->catID = $catID;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $uid = $this->user->getId();

        $builder
            ->add(
                'title', 'text', array(
                    'label' => 'Question.title',
                    'attr'  => array( 'placeholder' => 'Question.title')
                )
            )
            ->add(
                'category', 'entity', array(
                    'class' => 'UJM\\ExoBundle\\Entity\\Category',
                    'label' => 'Category.value',
                    'required' => false,
                    'query_builder' => function (CategoryRepository $cr) use ($uid) {
                        if ($this->catID == -1) {
                            return $cr->getUserCategory($uid);
                        } else {
                            return $cr->createQueryBuilder('c')
                                ->where('c.id = ?1')
                                ->setParameter(1, $this->catID);
                        }
                    }
                )
            )
            ->add('description', 'tinymce', array(
                    'label' => 'Question.description', 'required' => false
                )
            )
            ->add(
                'model', 'checkbox', array(
                    'required' => false,
                    'label' => 'Question.model'
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Question',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_questiontype';
    }

}