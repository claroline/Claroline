<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\LtiBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\LtiBundle\Entity\LtiApp;

class LtiAppSerializer
{
    use SerializerTrait;

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/lti/app.json';
    }

    public function getName()
    {
        return 'lti_app';
    }

    /**
     * @param LtiApp $app
     * @param array  $options
     *
     * @return array
     */
    public function serialize(LtiApp $app, array $options = [])
    {
        $serialized = [
            'id' => $app->getUuid(),
            'title' => $app->getTitle(),
            'url' => $app->getUrl(),
            'description' => $app->getDescription(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'appKey' => $app->getAppkey(),
                'secret' => $app->getSecret(),
            ]);
        }

        return $serialized;
    }

    /**
     * @param array  $data
     * @param LtiApp $app
     *
     * @return LtiApp
     */
    public function deserialize($data, LtiApp $app)
    {
        $this->sipe('id', 'setUuid', $data, $app);
        $this->sipe('title', 'setTitle', $data, $app);
        $this->sipe('url', 'setUrl', $data, $app);
        $this->sipe('appKey', 'setAppkey', $data, $app);
        $this->sipe('secret', 'setSecret', $data, $app);
        $this->sipe('description', 'setDescription', $data, $app);

        return $app;
    }
}
