<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                    'label' => 'title',
                    'required' => false,
                    'attr'  => array( 'placeholder' => 'question_title'),
                    'translation_domain' => 'ujm_exo'
                )
            )
            ->add(
                'category', 'entity', array(
                    'class' => 'UJM\\ExoBundle\\Entity\\Category',
                    'label' => 'Category.value',
                    'required' => false,
                    'empty_value' => 'choose_category',

                    'query_builder' => function (CategoryRepository $cr) use ($uid) {
                        if ($this->catID === -1) {
                            return $cr->getUserCategory($uid);
                        } else {
                            return $cr->createQueryBuilder('c')
                                ->where('c.id = ?1')
                                ->setParameter(1, $this->catID);
                        }
                    }
                )
            )
            ->add('description', 'textarea', array(
                    'label' => 'question_description',
                    'required' => false,
                    'attr'  => array( 'placeholder' => 'question_description',
                                      'class'=> 'form-control',
                                      'data-new-tab' => 'yes'
                                    )
                )
            )
            ->add(
                'model', 'checkbox', array(
                    'required' => false,
                    'label' => 'question_model'
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
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
