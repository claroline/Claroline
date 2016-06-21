<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Contact;

use Claroline\CoreBundle\Entity\Contact\Options;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionsType extends AbstractType
{
    private $options;

    public function __construct(Options $options)
    {
        $this->options = $options->getOptions();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $showAllMyContacts = !isset($this->options['show_all_my_contacts']) ||
            $this->options['show_all_my_contacts'];
        $showAllVisibleUsers = !isset($this->options['show_all_visible_users']) ||
            $this->options['show_all_visible_users'];
        $showUsername = !isset($this->options['show_username']) ||
            $this->options['show_username'];
        $showMail = isset($this->options['show_mail']) &&
            $this->options['show_mail'];
        $showPhone = isset($this->options['show_phone']) &&
            $this->options['show_phone'];
        $showPicture = !isset($this->options['show_picture']) ||
            $this->options['show_picture'];

        $builder->add(
            'showAllMyContacts',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'show_all_my_contacts',
                'data' => $showAllMyContacts,
            )
        );
        $builder->add(
            'showAllVisibleUsers',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'show_all_visible_users',
                'data' => $showAllVisibleUsers,
            )
        );
        $builder->add(
            'showPicture',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'show_picture',
                'data' => $showPicture,
            )
        );
        $builder->add(
            'showUsername',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'show_username',
                'data' => $showUsername,
            )
        );
        $builder->add(
            'showMail',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'show_mail',
                'data' => $showMail,
            )
        );
        $builder->add(
            'showPhone',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'show_phone',
                'data' => $showPhone,
            )
        );
    }

    public function getName()
    {
        return 'contact_options_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
