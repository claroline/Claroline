<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ThemeBundle\Entity\AppearanceParameters;

class AppearanceParametersSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return AppearanceParameters::class;
    }

    /**
     * Serializes an Organization entity for the JSON api.
     *
     * @param AppearanceParameters $parameters - the parameters to serialize
     *
     * @return array - the serialized representation of the workspace
     */
    public function serialize(AppearanceParameters $parameters, array $options = [])
    {
        return [
            'theme' => null,
            'icons' => null,
            'logo' => null,
            'display' => [
                'breadcrumb' => $this->config->getParameter('display.breadcrumb'),
            ],
            'header' => [
                'menus' => array_unique(array_values($this->configHandler->getParameter('header'))),
                'display' => [
                    'name' => $this->configHandler->getParameter('name_active'),
                    'about' => $this->configHandler->getParameter('show_about_button'),
                    'help' => $this->configHandler->getParameter('show_help_button'),
                ],
            ],
            'footer' => [
                'content' => $this->configHandler->getParameter('footer.content'),
                'display' => [
                    'locale' => $this->configHandler->getParameter('footer.show_locale'),
                    'help' => $this->configHandler->getParameter('footer.show_help'),
                    'termsOfService' => $this->configHandler->getParameter('footer.show_terms_of_service'),
                ],
            ],

            'posters' => [
                'ratio' => '5:1',
            ],
        ];
    }

    public function deserialize($data, AppearanceParameters $parameters, array $options = [])
    {
    }
}
