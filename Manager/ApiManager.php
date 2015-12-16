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
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service("claroline.manager.api_manager")
 * This service allows 2 instances of claroline-connect to communicate through their REST api.
 * The REST api requires an oauth authentication (wich is why the $id/$secret combination is required)
 */
class ApiManager
{

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "oauthManager" = @DI\Inject("claroline.manager.oauth_manager"),
     *     "curlManager"  = @DI\Inject("claroline.manager.curl_manager"),
     *     "viewHandler"  = @DI\Inject("fos_rest.view_handler"),
     *     "container"    = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ObjectManager $om,
        OauthManager $oauthManager,
        CurlManager $curlManager,
        $viewHandler,
        $container
    )
    {
        $this->om = $om;
        $this->oauthManager = $oauthManager;
        $this->curlManager  = $curlManager;
        $this->viewHandler  = $viewHandler;
        $this->container    = $container;
    }

    /**
     * Legacy method. Please use query() instead.
     * @deprecated
     */
    public function url($token, $url, $payload = null, $type = 'GET')
    {
        $this->validateUrl($url);

        switch (get_class($token)) {
            case 'Claroline\CoreBundle\Entity\Oauth\FriendRequest': return $this->adminQuery($token, $url, $payload, $type);
            //maybe later, we'll use this method to fetch resources & stuff from an other platform...
            //case 'Claroline\CoreBundle\Entity\Oauth\UserToken': return $this->userQuery($token, $url, $payload, $type);
        }
    }

    /* @see above
    private function userQuery(UserOauth $token, $url, $payload, $type)
    {
        return '';
    }*/

    private function adminQuery(FriendRequest $request, $url, $payload = null, $type = 'GET')
    {
        $access = $request->getClarolineAccess();
        if ($access === null) throw new \Exception('The oauth tokens were lost. Please ask for a new authentication.');
        $firstTry = $request->getHost() . '/' . $url . '?access_token=' . $access->getAccessToken();
        $serverOutput = $this->curlManager->exec($firstTry, $payload, $type);
        $json = json_decode($serverOutput, true);

        if ($json) {
            if (array_key_exists('error', $json)) {
                if ($json['error'] === 'access_denied' || $json['error'] === 'invalid_grant') {
                    $access = $this->oauthManager->connect($request->getHost(), $access->getRandomId(), $access->getSecret(), $access->getFriendRequest());
                    $secondTry = $request->getHost() . '/' . $url . '?access_token=' . $access->getAccessToken();
                    $serverOutput = $this->curlManager->exec($secondTry, $payload, $type);
                }
            }
        }

        return $serverOutput;
    }

    public function formEncode($entity, Form $form, AbstractType $formType)
    {
        $baseName = $formType->getName();
        $payload = array();

        foreach ($form->getIterator() as $el) {
            if (is_array($entity)) {
                $payload[$baseName . '[' . $el->getName() . ']'] = $entity[$el->getName()];
            }
        }

        return $payload;
    }

    //helper for the API controllers methods. We only do this in case of html request
    public function handleFormView($template, $form, array $options = array())
    {
        $httpCode = isset($options['http_code']) ? $options['http_code']: 200;

        return $form->isValid() ?
            $this->createSerialized($options['extra_parameters']):
            $this->createFormView($template, $form, $httpCode);

    }

    private function createFormView($template, $form, $formHttpCode)
    {
        $formHttpCode = $formHttpCode ?:200;
        $view = View::create($form, $formHttpCode);
        $view->setTemplate($template);
        $view->setFormat($this->container->get('request')->getRequestFormat());

        return $this->viewHandler->handle($view);
    }

    private function createSerialized($data)
    {
        $context = new SerializationContext();
        $format = $this->container->get('request')->getRequestFormat();
        $format = $format === 'html' ? 'json': $format;
        $context->setGroups('api');
        $content = $this->container->get('serializer')->serialize($data, $format, $context);
        $response = new Response($content);
        $response->headers->set('Content-Type', $this->container->get('request')->getMimeType($format));

        return $response;
    }

    private function validateUrl($url)
    {

    }
}
