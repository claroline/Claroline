<?php

namespace Claroline\CommunityBundle\Validator;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CommunityBundle\Serializer\ProfileSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\UserManager;

class UserValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly PlatformConfigurationHandler $config,
        private readonly UserManager $manager,
        private readonly FacetManager $facetManager,
        private readonly ProfileSerializer $profileSerializer
    ) {
    }

    public static function getClass(): string
    {
        return User::class;
    }

    public function getUniqueFields(): array
    {
        return [
            'username' => 'username',
            'email' => 'email',
        ];
    }

    public function validate(array $data, string $mode, array $options = []): array
    {
        $errors = [];

        // implements something cleaner later
        if (ValidatorProvider::UPDATE === $mode && !isset($data['id'])) {
            return $errors;
        }

        if (ValidatorProvider::CREATE === $mode) {
            // check the platform user limit
            if ($this->manager->hasReachedLimit()) {
                $errors[] = [
                    'path' => '',
                    'message' => 'The user limit of the platform has been reached.',
                ];
            }
        }

        // validate username
        if ($this->config->getParameter('community.username')) {
            // validate username format
            $regex = $this->config->getParameter('username_regex');
            if (isset($data['username']) && $regex && !preg_match($regex, $data['username'])) {
                $errors[] = [
                    'path' => 'username',
                    'message' => 'The username '.$data['username'].' contains illegal characters.',
                ];
            }
        }

        // Password check
        if (isset($data['plainPassword'])) {
            $errors = array_merge($errors, $this->validatePasswordCheck($data['plainPassword']));
        }

        // todo validate Facet values
        if (in_array(Options::VALIDATE_FACET, $options)) {
            $facets = $this->profileSerializer->serialize(in_array(Options::REGISTRATION, $options) ? [Options::REGISTRATION] : []);
            $allFields = [];
            $required = [];

            foreach ($facets as $facet) {
                foreach ($facet['sections'] as $section) {
                    foreach ($section['fields'] as $field) {
                        $allFields[] = $field;
                        if ($field['required']) {
                            $required[] = $field;
                        }
                    }
                }
            }

            foreach ($required as $field) {
                if ($this->facetManager->isFieldDisplayed($field, $allFields, $data) && !ArrayUtils::has($data, 'profile.'.$field['id'])) {
                    $errors[] = [
                        'path' => 'profile/'.$field['id'],
                        'message' => 'The field '.$field['label'].' is required',
                    ];
                }
            }
        }

        return $errors;
    }

    private function validatePasswordCheck(string $password): array
    {
        $errors = [];
        $authenticationParameters = $this->om->getRepository(AuthenticationParameters::class)->findOneBy([]);
        if ($authenticationParameters) {
            if ($authenticationParameters->getMinLength() > 0 && strlen($password) < $authenticationParameters->getMinLength()) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must be at least '.$authenticationParameters->getMinLength().' characters long.',
                ];
            }

            if ($authenticationParameters->getRequireLowercase() && !preg_match('/[a-z]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one lowercase letter.',
                ];
            }

            if ($authenticationParameters->getRequireUppercase() && !preg_match('/[A-Z]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one uppercase letter.',
                ];
            }

            if ($authenticationParameters->getRequireNumber() && !preg_match('/[0-9]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one number.',
                ];
            }

            if ($authenticationParameters->getRequireSpecialChar() && !preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors[] = [
                    'path' => 'plainPassword',
                    'message' => 'The password must contain at least one special character.',
                ];
            }
        }

        return $errors;
    }
}
