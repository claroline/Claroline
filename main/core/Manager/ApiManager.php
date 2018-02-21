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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;
use FOS\RestBundle\View\View;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
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
    ) {
        $this->om = $om;
        $this->oauthManager = $oauthManager;
        $this->curlManager = $curlManager;
        $this->viewHandler = $viewHandler;
        $this->container = $container;
    }

    /**
     * Legacy method. Please use query() instead.
     *
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
        if (null === $access) {
            throw new \Exception('The oauth tokens were lost. Please ask for a new authentication.');
        }
        $firstTry = $request->getHost().'/'.$url.'?access_token='.$access->getAccessToken();
        $serverOutput = $this->curlManager->exec($firstTry, $payload, $type);
        $json = json_decode($serverOutput, true);

        if ($json) {
            if (array_key_exists('error', $json)) {
                if ('access_denied' === $json['error'] || 'invalid_grant' === $json['error']) {
                    $access = $this->oauthManager->connect($request->getHost(), $access->getRandomId(), $access->getSecret(), $access->getFriendRequest());
                    $secondTry = $request->getHost().'/'.$url.'?access_token='.$access->getAccessToken();
                    $serverOutput = $this->curlManager->exec($secondTry, $payload, $type);
                }
            }
        }

        return $serverOutput;
    }

    public function formEncode($entity, Form $form, AbstractType $formType)
    {
        $baseName = $formType->getName();
        $payload = [];

        foreach ($form->getIterator() as $el) {
            if (is_array($entity)) {
                $payload[$baseName.'['.$el->getName().']'] = $entity[$el->getName()];
            }
        }

        return $payload;
    }

    //helper for the API controllers methods. We only do this in case of html request
    public function handleFormView($template, $form, array $options = [])
    {
        $httpCode = isset($options['http_code']) ? $options['http_code'] : 200;
        $parameters = isset($options['form_view']) ? $options['form_view'] : [];

        if (isset($options['extra_infos'])) {
            $parameters['extraInfos'] = $options['extra_infos'];
        }
        $serializerGroup = isset($options['serializer_group']) ? $options['serializer_group'] : 'api';

        return $form->isValid() ?
            $this->createSerialized($options['extra_parameters'], $serializerGroup) :
            $this->createFormView($template, $form, $httpCode, $parameters);
    }

    private function createFormView($template, $form, $formHttpCode, $parameters)
    {
        $formHttpCode = $formHttpCode ?: 200;
        $view = View::create($form, $formHttpCode);
        $view->setTemplate($template);
        $view->setTemplateData($parameters);
        $view->setFormat($this->container->get('request')->getRequestFormat());

        return $this->viewHandler->handle($view);
    }

    private function createSerialized($data, $serializerGroup)
    {
        $context = new SerializationContext();
        $format = $this->container->get('request')->getRequestFormat();
        $format = 'html' === $format ? 'json' : $format;
        $context->setGroups($serializerGroup);
        $content = $this->container->get('serializer')->serialize($data, $format, $context);
        $response = new Response($content);
        $response->headers->set('Content-Type', $this->container->get('request')->getMimeType($format));

        return $response;
    }

    public function getParameters($name, $class)
    {
        $request = $this->container->get('request');
        $data = $entities = [];

        if ($request->request->has($name)) {
            $data = $request->request->get($name);
        }
        if ($request->query->has($name)) {
            $data = $request->query->get($name);
        }

        foreach ($data as $id) {
            //make one big query later
            $entities[] = $this->om->getRepository($class)->find((int) $id);
        }

        return $entities;
    }

    public function getParametersByUuid($name, $class)
    {
        $request = $this->container->get('request');
        $data = $entities = [];

        if ($request->request->has($name)) {
            $data = $request->request->get($name);
        }
        if ($request->query->has($name)) {
            $data = $request->query->get($name);
        }

        foreach ($data as $uuid) {
            //make one big query later
            $entities[] = $this->om->getRepository($class)->findOneByUuid($uuid);
        }

        return $entities;
    }

    private function validateUrl($url)
    {
    }
}
