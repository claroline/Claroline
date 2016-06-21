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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Validator\Constraints\CsvUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportUserType extends AbstractType
{
    private $mode;
    private $showRoles;

    public function __construct($showRoles = false, $mode = 0)
    {
        $this->mode = $mode;
        $this->showRoles = $showRoles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            'file',
            array(
                'required' => true,
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(),
                    new File(),
                    new CsvUser($this->mode),
                ),
            )
        )->add(
            'mode',
            'choice',
            array(
                'label' => 'mode',
                'choices' => array('create' => 'create_only', 'update' => 'create_and_update'),
                'required' => true,
            )
        )->add(
            'sendMail',
            'checkbox',
            array(
                'label' => 'send_mail',
                'required' => false,
            )
        )->add(
            'enable_mail_notification',
            'checkbox',
            array(
                'label' => 'user_enable_mail_notification_label',
                'required' => false,
            )
        );

        if ($this->showRoles) {
            $builder->add(
                'roles',
                'entity',
                array(
                    'required' => false,
                    'label' => 'roles',
                    'mapped' => false,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'choice_translation_domain' => true,
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                            $query = $er->createQueryBuilder('r')
                                ->where('r.type = '.Role::PLATFORM_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'")
                                ->andWhere("r.name != 'ROLE_USER'");

                            return $query;
                        },
                )
            );
        }
    }

    public function getName()
    {
        return 'import_user_file';
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
