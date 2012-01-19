<?php

namespace Claroline\CoreBundle\History;

class Context
{
    private $name;
    private $uri;
    
    public function __construct($name, $uri)
    {
        $this->name = $name;
        $this->uri = $uri;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
}