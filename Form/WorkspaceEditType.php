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
        $isAdmin = false
    )
    {
        $this->username = $username;
        $this->creationDate = $creationDate;
        $this->number = $number;
        $this->storageSpaceUsed = $storageSpaceUsed;
        $this->countResources = $countResources;
        $this->isAdmin = $isAdmin;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder->add('name', 'text', array('required' => true));
        $builder->add('code', 'text', array('required' => true));
        $builder->add(
                'creationDate',
                'text',
                array(
                    'disabled' => 'disabled',
                    'data' => $this->creationDate
                )
            );
        $builder->add('creator', 'text', array('disabled' => 'disabled', 'data' => $this->username));
        if (isset($options['theme_options']['tinymce']) and !$options['theme_options']['tinymce']) {
            $builder->add(
                'description',
                'textarea',
                array('required' => false)
            );
        } else {
            $builder->add('description', 'tinymce', array('required' => false));
        }
        $builder->add('displayable', 'checkbox', array('required' => false));
        $builder->add('selfRegistration', 'checkbox', array('required' => false));
        $builder->add('registrationValidation', 'checkbox', array('required' => false));
        $builder->add('selfUnregistration', 'checkbox', array('required' => false));
        $builder->add('number', 'text', array('disabled' => 'disabled', 'data' => $this->number, 'mapped' => false));

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
                'translation_domain' => 'platform'
                )
        );
    }
}
