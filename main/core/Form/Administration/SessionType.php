<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'session_storage_type',
                ChoiceType::class,
                [
                    'choices' => [
                        'native' => 'files',
                        'claro_pdo' => 'database',
                        'pdo' => 'external_pdo_database',
                    ],
                    'label' => 'storage_type',
                    'data' => $options['session_type'],
                    'disabled' => isset($options['locked_params']['session_storage_type']),
                ]
            );

        $builder
            ->add(
                'session_db_dsn',
                TextType::class,
                [
                    'label' => 'DSN',
                    'required' => false,
                    'constraints' => $this->notBlankIfExternal($options),
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbDsn'),
                    'disabled' => isset($options['locked_params']['session_db_dsn']),
                ]
            )
            ->add(
                'session_db_user',
                TextType::class,
                [
                    'label' => 'user',
                    'required' => false,
                    'constraints' => $this->notBlankIfExternal($options),
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbUser'),
                    'disabled' => isset($options['locked_params']['session_db_user']),
                ]
            )
            ->add(
                'session_db_password',
                PasswordType::class,
                [
                    'label' => 'password',
                    'required' => false,
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbPassword'),
                    'disabled' => isset($options['locked_params']['session_db_password']),
                ]
            )
            ->add(
                'session_db_table',
                TextType::class,
                [
                    'label' => 'db_table',
                    'required' => false,
                    'constraints' => $this->notBlankIfExternal($options),
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbTable'),
                    'disabled' => isset($options['locked_params']['session_db_table']),
                ]
            )
            ->add(
                'session_db_id_col',
                TextType::class,
                [
                    'label' => 'id_col',
                    'required' => false,
                    'constraints' => $this->notBlankIfExternal($options),
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbIdCol'),
                    'disabled' => isset($options['locked_params']['session_db_id_col']),
                ]
            )
            ->add(
                'session_db_data_col',
                TextType::class,
                [
                    'label' => 'data_col',
                    'required' => false,
                    'constraints' => $this->notBlankIfExternal($options),
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbDataCol'),
                    'disabled' => isset($options['locked_params']['session_db_data_col']),
                ]
            )
            ->add(
                'session_db_time_col',
                TextType::class,
                [
                    'label' => 'time_col',
                    'required' => false,
                    'constraints' => $this->notBlankIfExternal($options),
                    'attr' => $this->hiddenIfNotExternal($options),
                    'data' => $this->getConfigValue($options, 'sessionDbTimeCol'),
                    'disabled' => isset($options['locked_params']['session_db_time_col']),
                ]
            );

        $builder->add(
            'cookie_lifetime',
            NumberType::class,
            [
                'required' => true,
                'label' => 'cookie_lifetime',
                'constraints' => new GreaterThanOrEqual(['value' => 60]),
                'data' => $this->getConfigValue($options, 'cookieLifetime'),
                'disabled' => isset($options['locked_params']['cookie_lifetime']),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'translation_domain' => 'platform',
          'session_type' => 'native',
          'config' => null,
          'locked_params' => [],
        ]);
    }

    private function notBlankIfExternal(array $options)
    {
        if ('pdo' === $options['session_type']) {
            return new NotBlank();
        }

        return [];
    }

    private function hiddenIfNotExternal(array $options)
    {
        if ('pdo' !== $options['session_type']) {
            return ['display_row' => false];
        }

        return [];
    }

    private function getConfigValue(array $options, $parameter)
    {
        if ($options['config']) {
            $method = 'get'.ucfirst($parameter);

            return $options['config']->{$method}();
        }
    }
}
