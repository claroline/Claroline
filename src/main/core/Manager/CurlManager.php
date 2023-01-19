<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CurlManager
{
    public function exec($url, $payload = null, $type = 'GET', $options = [], $autoClose = true, &$ch = null)
    {
        $options[CURLOPT_RETURNTRANSFER] = true;

        if ('GET' === $type && !empty($payload)) {
            $url .= '?'.http_build_query($payload);
        }

        $options[CURLOPT_URL] = $url;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        switch ($type) {
            case 'POST':
                $this->setPostCurl($ch, $payload);
                break;
            case 'PUT':
                $this->setPutCurl($ch, $payload);
                break;
            case 'DELETE':
                $this->setDeleteCurl($ch);
                break;
        }

        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        $serverOutput = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($autoClose) {
            curl_close($ch);
        }

        switch ($httpCode) {
            case 401:
            case 403:
                throw new AccessDeniedException($serverOutput);
            case 404:
                throw new NotFoundHttpException($serverOutput);
            case 422:
                throw new InvalidDataException($serverOutput);
            case 500:
                throw new \Exception($serverOutput);
        }

        return $serverOutput;
    }

    private function setPostCurl($ch, $payload)
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    private function setPutCurl($ch, $payload)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    private function setDeleteCurl($ch)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
}
