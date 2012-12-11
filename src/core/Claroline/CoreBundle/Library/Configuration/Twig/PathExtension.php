<?php


namespace Claroline\CoreBundle\Library\Configuration\Twig;

use Twig_Extension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PathExtension extends Twig_Extension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'path_res' => new \Twig_Function_Method($this, 'getPath'),
            'url_res' => new \Twig_Function_Method($this, 'getUrl')
        );
    }

    public function getPath ($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, false).'?activity=true';
    }

    public function getUrl($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, true).'?activity=true';
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'path_extension';
    }
}

