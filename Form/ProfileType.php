<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    private $platformRoles;
    private $isFormCreation;

    public function __construct($platformRoles, $isFormCreation = false)
    {
        $this->platformRoles = new ArrayCollection($platformRoles);
        $this->isFormCreation = $isFormCreation;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($this->isFormCreation) {
            $builder->add('firstName', 'text', array('read_only' => true))
                ->add('lastName', 'text', array('read_only' => true))
                ->add('username', 'text', array('read_only' => true))
                ->add('administrativeCode', 'text', array('required' => false, 'read_only' => true))
                ->add('plainPassword', 'repeated', array('type' => 'password'))
                ->add('mail', 'email', array('required' => false))
                ->add('phone', 'text', array('required' => false));
            $builder->add(
                'platformRoles',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->platformRoles,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'disabled' => true,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            );
        } else {
            $builder->add('firstName', 'text')
                ->add('lastName', 'text')
                ->add('username', 'text')
                ->add('administrativeCode', 'text', array('required' => false))
                ->add('plainPassword', 'repeated', array('type' => 'password'))
                ->add('mail', 'email', array('required' => false))
                ->add('phone', 'text', array('required' => false));
            $builder->add(
                'platformRoles',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->platformRoles,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            );
        }
        $builder->add(
            'pictureFile',
            'file',
            array(
                'required' => false,
                'constraints' => new Image(
                    array(
                        'minWidth' => 50,
                        'maxWidth' => 800,
                        'minHeight' => 50,
                        'maxHeight' => 800,
                    )
                )
            )
        );
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
                )
        );
    }
}
