<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Claroline\CoreBundle\Entity\User;

class InteractionGraphicType extends AbstractType
{
    private $user;
    private $catID;
    private $docID;

    public function __construct(User $user, $catID = -1, $docID = -1)
    {
        $this->user = $user;
        $this->catID = $catID;
        $this->docID = $docID;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $id = $this->user->getId();

        $builder
            ->add(
                'interaction', new InteractionType($this->user, $this->catID)
            )
            ->add(
                'document', 'entity', array(
                    'class' => 'UJMExoBundle:Document',
                    'property' => 'label',
                  // Request to get the pictures matching to the user_id
                    'query_builder' => function (\UJM\ExoBundle\Repository\DocumentRepository $repository) use ($id) {
                        if ($this->docID == -1) {
                            return $repository->createQueryBuilder('d')
                                ->where('d.user = ?1')
                                ->andwhere('d.type like \'%.png%\' OR d.type like \'%.jpeg%\' '
                                        .'OR d.type like \'%.jpg%\' OR d.type like \'%.gif%\' OR d.type like \'%.bmp%\'')
                                ->setParameter(1, $id);
                        } else {
                            return $repository->createQueryBuilder('d')
                                ->where('d.id = ?1')
                                ->setParameter(1, $this->docID);
                        }
                    },
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\InteractionGraphic',
                'cascade_validation' => true,
                'translation_domain' => 'ujm_exo',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_interactiongraphictype';
    }
}
