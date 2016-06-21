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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Validator\Constraints\FileSize;

class WorkspaceEditType extends AbstractType
{
    private $username;
    private $creationDate;
    private $number;
    private $storageSpaceUsed;
    private $countResources;
    private $isAdmin;
    private $expirationDate;

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
        $expirationDate = null
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
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder->add('name', 'text', array('required' => true, 'label' => 'name'));
        $builder->add('code', 'text', array('required' => true, 'label' => 'code'));
        $builder->add(
                'creationDate',
                'text',
                array(
                    'disabled' => 'disabled',
                    'data' => $this->creationDate,
                    'label' => 'creation_date',
                )
            );
        if ($this->expirationDate) {
            $params = array(
                'label' => 'expiration_date',
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'input' => 'datetime',
                'attr' => array(
                    'class' => 'datepicker input-small',
                    'data-date-format' => 'dd-mm-yyyy',
                    'autocomplete' => 'off',
                ),
            );

            if (!$this->isAdmin) {
                $params['disabled'] = 'disabled';
            }

            $builder->add(
                'endDate',
                'datepicker',
                $params
            );
        }
        $builder->add('creator', 'text', array('disabled' => 'disabled', 'data' => $this->username, 'label' => 'creator'));
        if (isset($options['theme_options']['tinymce']) && !$options['theme_options']['tinymce']) {
            $builder->add(
                'description',
                'textarea',
                array('required' => false, 'label' => 'description')
            );
        } else {
            $builder->add('description', 'tinymce', array('required' => false, 'label' => 'description'));
        }
        $builder->add('displayable', 'checkbox', array('required' => false, 'label' => 'displayable_in_workspace_list'));
        $builder->add('selfRegistration', 'checkbox', array('required' => false, 'label' => 'public_registration'));
        $builder->add('registrationValidation', 'checkbox', array('required' => false, 'label' => 'registration_validation'));
        $builder->add('selfUnregistration', 'checkbox', array('required' => false, 'label' => 'public_unregistration'));

        if (!$this->isAdmin) {
            $builder->add('maxStorageSize', 'text', array('disabled' => 'disabled', 'label' => 'max_storage_size'));
        } else {
            $builder->add('maxStorageSize', 'text', array('label' => 'max_storage_size', 'constraints' => array(new FileSize())));
        }

        $builder->add('storageUsed', 'text', array('mapped' => false, 'disabled' => 'disabled', 'label' => 'storage_used', 'data' => $this->storageSpaceUsed));

        if (!$this->isAdmin) {
            $builder->add('maxUploadResources', 'text', array('disabled' => 'disabled', 'label' => 'max_amount_resources'));
        } else {
            $builder->add('maxUploadResources', 'text', array('label' => 'max_amount_resources'));
        }

        $builder->add('countResources', 'text', array('mapped' => false, 'disabled' => 'disabled', 'label' => 'count_resources', 'data' => $this->countResources));

        if (!$this->isAdmin) {
            $builder->add('maxUsers', 'text', array('disabled' => 'disabled', 'label' => 'workspace_max_users'));
        } else {
            $builder->add('maxUsers', 'text', array('label' => 'workspace_max_users'));
        }

        $builder->add('number', 'text', array('disabled' => 'disabled', 'data' => $this->number, 'mapped' => false, 'label' => 'registered_user_amount'));
    }

    public function getName()
    {
        return 'workspace_edit_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform',
                )
        );
    }
}
