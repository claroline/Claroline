<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Form;

use Claroline\CoreBundle\Form\Angular\AngularType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class FileSelectType extends AngularType
{
    private $forApi = false;
    private $ngAlias;

    public function __construct($ngAlias = 'cmc')
    {
        $this->ngAlias = $ngAlias;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'archive',
            'file',
            [
                'required' => true,
                'mapped' => false,
                'label' => 'file',
                'translation_domain' => 'platform',
                'constraints' => [
                    new NotBlank(),
                    new File(),
                ],
            ]
        );
    }

    public function getName()
    {
        return 'file_selection_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = ['translation_domain' => 'cursus'];

        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $default['ng-model'] = 'file';
        $default['ng-controllerAs'] = $this->ngAlias;
        $resolver->setDefaults($default);
    }

    public function enableApi()
    {
        $this->forApi = true;
    }
}
