<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Constraints\WorkspaceUniqueCode;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class WorkspaceType extends AbstractType
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;
        if (php_sapi_name() === 'cli') {
            $this->forApi = true;
        }

        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'name',
                    'constraints' => new NotBlank()
                )
            )
            ->add(
                'code',
                'text',
                array(
                    'constraints' => array(new WorkspaceUniqueCode(), new NotBlank()),
                    'label' => 'code'
                )
            )
            ->add(
                'description',
                isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce'] ?
                    'textarea' :
                    'tinymce',
                array('required' => false, 'label' => 'description')
            );

            if (!$this->forApi) {
                $builder->add(
                    'model',
                    'entity',
                    array(
                        'class' => 'ClarolineCoreBundle:Model\WorkspaceModel',
                        'query_builder' => function (EntityRepository $er) use ($user) {

                            return $er->createQueryBuilder('wm')
                                ->join('wm.users', 'u')
                                ->where('u.id = :userId')
                                ->setParameter('userId', $user->getId())
                                ->orderBy('wm.name', 'ASC');
                        },
                        'property' => 'nameAndWorkspace',
                        'required' => false,
                        'label' => 'model'
                    )
                );
            }
            $builder
                ->add('displayable', 'checkbox', array('required' => false, 'label' => 'displayable_in_workspace_list'))
                ->add('selfRegistration', 'checkbox', array('required' => false, 'label' => 'public_registration'))
                ->add('registrationValidation', 'checkbox', array('required' => false, 'label' => 'registration_validation'))
                ->add('selfUnregistration', 'checkbox', array('required' => false, 'label' => 'public_unregistration'));

        if ($this->forApi) {
            $builder->add(
                'maxStorageSize',
                'text',
                array(
                    'label' => 'max_storage_size',
                    'constraints' => array(new NotBlank())
                )
            );
            $builder->add(
                'maxUploadResources',
                'text',
                array(
                    'label' => 'max_amount_resources',
                    'constraints' => array(new NotBlank())
                )
            );
            $builder->add(
                'maxUsers',
                'text',
                array(
                    'label' => 'workspace_max_users',
                    'constraints' => array(new NotBlank())
                )
            );
            $params = array(
                'label' => 'expiration_date',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'input' => 'datetime',
                'attr' => array(
                    'class' => 'datepicker input-small',
                    'data-date-format' => 'dd-mm-yyyy',
                    'autocomplete' => 'off'
                ),
                'constraints' => array(new NotBlank())
            );

            $builder->add('endDate', 'datepicker', $params);
        }
    }

    public function getName()
    {
        return 'workspace_form';
    }

    public function enableApi()
    {
        $this->forApi = true;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array('translation_domain' => 'platform');
        if ($this->forApi) $default['csrf_protection'] = false;

        $resolver->setDefaults($default);
    }
}
