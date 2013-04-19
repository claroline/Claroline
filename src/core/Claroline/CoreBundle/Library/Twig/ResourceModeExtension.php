<?php

namespace Claroline\CoreBundle\Library\Twig;

use Twig_Extension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Claroline\CoreBundle\Library\Resource\Mode;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Adds the isPathMode var to the twig Globals. It's used by the
 * activity player to remove the resource context.
 *
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class ResourceModeExtension extends Twig_Extension
{
    private $generator;

    /**
     * @DI\InjectParams({
     *     "generator" = @DI\Inject("router")
     * })
     */
    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'is_path_mode' => Mode::$isPathMode
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            '_path' => new \Twig_Function_Method($this, 'getPath'),
            '_url' => new \Twig_Function_Method($this, 'getUrl')
        );
    }

    public function getPath($name, $parameters = array())
    {
        return $this->appendMode($this->generator->generate($name, $parameters, false));
    }

    public function getUrl($name, $parameters = array())
    {
        return $this->appendMode($this->generator->generate($name, $parameters, true));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'resource_mode_extension';
    }

    private function appendMode($path)
    {
        return $path . (Mode::$isPathMode ? '?_mode=path' : '');
    }
}