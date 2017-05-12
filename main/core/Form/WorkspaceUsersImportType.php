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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Validator\Constraints\CsvWorkspaceUserImport;
use Claroline\CoreBundle\Validator\Constraints\CsvWorkspaceUserImportByFullName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class WorkspaceUsersImportType extends AbstractType
{
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            'file',
            [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new File(),
                    $options['import_by_full_name'] ? new CsvWorkspaceUserImportByFullName($this->workspace) : new CsvWorkspaceUserImport($this->workspace),
                ],
            ]
        );
    }

    public function getName()
    {
        return 'import_user_file';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'platform',
            'import_by_full_name' => false,
        ]);
    }
}
