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

use Claroline\CoreBundle\Validator\Constraints\FileSize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceEditType extends AbstractType
{
    private $username;
    private $creationDate;
    private $number;
    private $storageSpaceUsed;
    private $countResources;
    private $isAdmin;
    private $expirationDate;
    private $startDate;

    /**
     * Constructor.
     *
     * @param string $username
     */
    public function __construct(
        $username = null,
        $creationDate = null,
        $number = null,
        $storageSpaceUsed = null,
        $countResources = null,
        $isAdmin = false,
        $expirationDate = null,
        $startDate = null
    ) {
        $this->username = $username;
        $this->creationDate = $creationDate;
        $this->number = $number;
        $this->storageSpaceUsed = $storageSpaceUsed;
        $this->countResources = $countResources;
        $this->isAdmin = $isAdmin;
        $this->expirationDate = $expirationDate;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = [];
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder->add('name', 'text', ['required' => true, 'label' => 'name']);
        $builder->add('code', 'text', ['required' => true, 'label' => 'code']);
        $builder->add(
                'creationDate',
                'text',
                [
                    'disabled' => 'disabled',
                    'data' => $this->creationDate,
                    'label' => 'creation_date',
                ]
            );

        $params = [
            'label' => 'expiration_date',
            'format' => 'dd-MM-yyyy',
            'required' => false,
            'widget' => 'single_text',
            'input' => 'datetime',
            'attr' => [
                'class' => 'datepicker input-small',
                'data-date-format' => 'dd-mm-yyyy',
                'autocomplete' => 'off',
            ],
        ];

        if (!$this->isAdmin) {
            $params['disabled'] = 'disabled';
        }

        $params['label'] = 'opening_date';
        $builder->add(
            'startDate',
            'datepicker',
            $params
        );
        $params['label'] = 'expiration_date';
        $builder->add(
            'endDate',
            'datepicker',
            $params
        );

        $builder->add('creator', 'text', ['disabled' => 'disabled', 'data' => $this->username, 'label' => 'creator']);
        if (isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce']) {
            $builder->add(
                'description',
                'textarea',
                ['required' => false, 'label' => 'description']
            );
        } else {
            $builder->add('description', 'tinymce', ['required' => false, 'label' => 'description']);
        }
        $builder->add('displayable', 'checkbox', ['required' => false, 'label' => 'displayable_in_workspace_list']);
        $builder->add('selfRegistration', 'checkbox', ['required' => false, 'label' => 'public_registration']);
        $builder->add('registrationValidation', 'checkbox', ['required' => false, 'label' => 'registration_validation']);
        $builder->add('selfUnregistration', 'checkbox', ['required' => false, 'label' => 'public_unregistration']);

        if (!$this->isAdmin) {
            $builder->add('maxStorageSize', 'text', ['disabled' => 'disabled', 'label' => 'max_storage_size']);
        } else {
            $builder->add('maxStorageSize', 'text', ['label' => 'max_storage_size', 'constraints' => [new FileSize()]]);
        }

        $builder->add('storageUsed', 'text', ['mapped' => false, 'disabled' => 'disabled', 'label' => 'storage_used', 'data' => $this->storageSpaceUsed]);

        if (!$this->isAdmin) {
            $builder->add('maxUploadResources', 'text', ['disabled' => 'disabled', 'label' => 'max_amount_resources']);
        } else {
            $builder->add('maxUploadResources', 'text', ['label' => 'max_amount_resources']);
        }

        $builder->add('countResources', 'text', ['mapped' => false, 'disabled' => 'disabled', 'label' => 'count_resources', 'data' => $this->countResources]);

        if (!$this->isAdmin) {
            $builder->add('maxUsers', 'text', ['disabled' => 'disabled', 'label' => 'workspace_max_users']);
        } else {
            $builder->add('maxUsers', 'text', ['label' => 'workspace_max_users']);
        }

        $builder->add('number', 'text', ['disabled' => 'disabled', 'data' => $this->number, 'mapped' => false, 'label' => 'registered_user_amount']);

        $builder->add(
            'organizations',
            'organization_picker',
            [
               'label' => 'organizations',
            ]
        );
    }

    public function getName()
    {
        return 'workspace_edit_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            [
                'translation_domain' => 'platform',
                ]
        );
    }
}
