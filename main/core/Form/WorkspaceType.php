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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class WorkspaceType extends AbstractType
{
    private $user;

    public function __construct(User $user = null)
    {
        $this->user = $user;
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;

        if ('cli' === php_sapi_name()) {
            $this->forApi = true;
        }

        if ($this->forApi) {
            $codeConstraints = [new NotBlank()];
        } else {
            $codeConstraints = [new WorkspaceUniqueCode(), new NotBlank()];
        }

        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'name',
                    'constraints' => new NotBlank(),
                ]
            )
            ->add(
                'code',
                TextType::class,
                [
                    'constraints' => $codeConstraints,
                    'label' => 'code',
                ]
            )
            ->add(
                'description',
                isset($options['attr']['tinymce']) && !$options['attr']['tinymce'] ?
                    'textarea' :
                    'tinymce',
                ['required' => false, 'label' => 'description']
            );

        $builder
            ->add('displayable', CheckboxType::class, ['required' => false, 'label' => 'displayable_in_workspace_list'])
            ->add('selfRegistration', CheckboxType::class, ['required' => false, 'label' => 'public_registration'])
            ->add('registrationValidation', CheckboxType::class, ['required' => false, 'label' => 'registration_validation'])
            ->add('selfUnregistration', CheckboxType::class, ['required' => false, 'label' => 'public_unregistration'])
            ->add('disabledNotifications', CheckboxType::class, ['required' => false, 'label' => 'disable_workspace_notifications'])
            ->add('organizations', 'organization_picker', ['label' => 'organizations']);

        if (!$this->forApi) {
            $options = [
               'class' => 'ClarolineCoreBundle:Workspace\Workspace',
               'property' => 'code',
               'required' => false,
               'label' => 'base_model',
               'mapped' => false,
            ];

            if (!$user->hasRole('ROLE_ADMIN')) {
                $options['query_builder'] = function (EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('w')
                     ->leftJoin('w.roles', 'r')
                     ->leftJoin('r.users', 'u')
                     ->where('u.id = :userId')
                     ->andWhere('w.model = true')
                     ->setParameter('userId', $user->getId())
                     ->orderBy('w.name', 'ASC');
                };
            } else {
                $options['query_builder'] = function (EntityRepository $er) {
                    return $er->createQueryBuilder('w')
                   ->where('w.model = true')
                   ->orderBy('w.name', 'ASC');
                };
            }

            $builder->add(
               'modelFrom',
               'entity',
               $options
            );

            $builder->add('model', CheckboxType::class, ['required' => false, 'label' => 'model']);
        }

        if ($this->forApi) {
            $builder->add(
                'maxStorageSize',
                TextType::class,
                [
                    'label' => 'max_storage_size',
                    'constraints' => [new NotBlank()],
                ]
            );
            $builder->add(
                'maxUploadResources',
                TextType::class,
                [
                    'label' => 'max_amount_resources',
                    'constraints' => [new NotBlank()],
                ]
            );
            $builder->add(
                'maxUsers',
                TextType::class,
                [
                    'label' => 'workspace_max_users',
                    'constraints' => [new NotBlank()],
                ]
            );
            $params = [
                'label' => 'expiration_date',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'input' => 'datetime',
                'attr' => [
                    'class' => 'datepicker input-small',
                    'data-date-format' => 'dd-mm-yyyy',
                    'autocomplete' => 'off',
                ],
            ];

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $default = ['translation_domain' => 'platform'];
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }

        $resolver->setDefaults($default);
    }
}
