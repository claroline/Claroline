<?php

namespace Claroline\AppBundle\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ApiDoc extends Annotation
{
    public $description = null;
    public $body = null;
    public $parameters = null;
    public $queryString = null;
    public $produce = null;
    public $response = null;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    public function getProduce()
    {
        return $this->produce;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
