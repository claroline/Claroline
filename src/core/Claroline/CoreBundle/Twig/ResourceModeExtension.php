<?php

namespace Claroline\CoreBundle\Twig;

use Twig_Extension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Resource\QueryStringWriter;
use Claroline\CoreBundle\Library\Resource\ModeAccessor;

/**
 * Adds a "is_path_mode" global and two alternative functions for url generation.
 *
 * @DI\Service()
 * @DI\Tag("twig.extension")
 */
class ResourceModeExtension extends Twig_Extension
{
    private $generator;
    private $writer;
    private $accessor;

    /**
     * @DI\InjectParams({
     *     "generator"  = @DI\Inject("router"),
     *     "writer"     = @DI\Inject("claroline.resource.query_string_writer"),
     *     "accessor"   = @DI\Inject("claroline.resource.mode_accessor")
     * })
     */
    public function __construct(
        UrlGeneratorInterface $generator,
        QueryStringWriter $writer,
        ModeAccessor $accessor
    )
    {
        $this->generator = $generator;
        $this->writer = $writer;
        $this->accessor = $accessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'resource_mode_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return array(
            'is_path_mode' => $this->accessor->isPathMode()
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

    /**
     * Generates a relative url for a given route. If query string parameters
     * related to the resource context (mode, workspace, breadcrumbs) were passed
     * in the current request, they will be appended to the generated url.
     *
     * @param string    $name       The route's name
     * @param array     $parameters The route's parameters
     * @return string
     */
    public function getPath($name, $parameters = array())
    {
        return $this->appendMode($this->generator->generate($name, $parameters, false));
    }

    /**
     * Generates an absolute url for a given route. If query string parameters
     * related to the resource context (mode, workspace, breadcrumbs) were passed
     * in the current request, they will be appended to the generated url.
     *
     * @param string    $name       The route's name
     * @param array     $parameters The route's parameters
     * @return string
     */
    public function getUrl($name, $parameters = array())
    {
        return $this->appendMode($this->generator->generate($name, $parameters, true));
    }

    private function appendMode($path)
    {
        if ('' !== $query = $this->writer->getQueryString()) {
            return "{$path}?{$query}";
        }

        return $path;
    }
}
