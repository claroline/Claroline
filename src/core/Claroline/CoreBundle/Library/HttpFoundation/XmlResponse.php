<?php

namespace Claroline\CoreBundle\Library\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class XmlResponse extends Response
{
    protected $data;

    public function __construct($data = null, $status = 200, $headers = array())
    {
        parent::__construct('', $status, $headers);

        if (null === $data) {
            $data = new \ArrayObject();
        }

        $this->setData($data);
    }

    /**
     * {@inheritDoc}
     */
    public static function create($data = null, $status = 200, $headers = array())
    {
        return new static($data, $status, $headers);
    }

    protected function setData($data = array())
    {
        $encoder = new XmlEncoder();
        $this->data = $encoder->encode($data, 'xml');

        return $this->update();
    }

    protected function update()
    {
        $this->setContent($this->data);
        $this->headers->set('Content-Type', 'text/xml');
    }
}