<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Form;

use Claroline\CoreBundle\Validator\Constraints\IcsFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportAgendaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            'file',
            [
                'required' => true,
                'mapped' => false,
                'label' => 'import_agenda_file',
                'constraints' => [
                    new NotBlank(),
                    new File(),
                    new IcsFile(),
                ],
           ]
        );
    }

    public function getName()
    {
        return 'import_agenda_file';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            [
                'translation_domain' => 'agenda',
                ]
        );
    }
}
