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

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;

/**
 * @DI\Service("claroline.manager.curl_manager")
 */
class CurlManager
{
    public function exec($url, $payload = null, $type = 'GET')
    {
        $url = trim($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        switch ($type) {
            case 'POST': $this->setPostCurl($ch, $payload); break;
            case 'PUT': $this->setPutCurl($ch, $payload); break;
        }

        //$qs = http_build_query(array('payload' => $payload));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $qs);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $serverOutput = curl_exec($ch);
        curl_close($ch);

        return $serverOutput;
    }

    /**
     * Transforms an entity into an array wich will be used by the 'post' curl request for a specified form.
     */
    public function formEncode($entity, AbstractType $formType)
    {
        $data = array();
    }

    private function setPostCurl($ch, $payload)
    {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->urlify($payload));
    }

    private function setPutCurl($ch, $payload)
    {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->urlify($payload));
    }

    private function urlify($payload)
    {
        $string = '';

        foreach ($payload as $key => $value) {
            $string .= $key.'='.$value.'&';
        }

        rtrim($string, '&');

        return $string;
    }
}
