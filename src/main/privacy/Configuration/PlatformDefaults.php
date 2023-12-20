<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PrivacyBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'privacy' => [
                'countryStorage' => 'FR',
                'dpo' => [
                    'name' => '',
                    'email' => '',
                    'address' => [
                        'street1' => '',
                        'street2' => null,
                        'postalCode' => '',
                        'city' => '',
                        'state' => '',
                        'country' => 'FR',
                    ],
                    'phone' => '',
                ],
                'tos' => [
                    'enabled' => true,
                    'template' => null
                ]
            ],
        ];
    }
}
