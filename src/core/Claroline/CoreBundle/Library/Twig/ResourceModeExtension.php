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
 * @DI\Service()
 * @DI\Tag("twig.extension")
 */
class ResourceModeExtension extends Twig_Extension
{
    private $generator;

    /**
     * @DI\InjectParams({
     *     "generator" = @DI\Inject("router"),
     *     "container" = @DI\Inject("service_container")
     * })
     */

    public function __construct(UrlGeneratorInterface $generator, $container)
    {
        $this->container = $container;
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
        $path = $this->appendMode($this->generator->generate($name, $parameters, false));
        $path = $this->appendBreadcrumbs($path);
        $path = $this->appendWorkspace($path);

        return $path;
    }

    public function getUrl($name, $parameters = array())
    {
        $url = $this->appendMode($this->generator->generate($name, $parameters, true));
        $url = $this->appendBreadcrumbs($url);
        $url = $this->appendWorkspace($url);

        return $url;
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

    private function appendBreadcrumbs($path)
    {
        $breadcrumbs = $this->container->get('request')->query->get('_breadcrumbs', array());

        if ($breadcrumbs != null) {
            $toAppend = '';
            for ($i = 0, $size = count($breadcrumbs); $i < $size; $i++) {
                if ($i === 0) {
                    $toAppend .= '?';
                } else {
                    $toAppend .= '&';
                }
                $toAppend .= '_breadcrumbs[]='. $breadcrumbs[$i];
            }
            $path .= $toAppend;
        }

        return $path;
    }

    private function appendWorkspace($path)
    {
        $workspace = $this->container->get('request')->query->get('_workspace');


        if ($workspace !== null) {
            if (count($this->container->get('request')->query->all()) > 0) {
                return $path . "&_workspace={$workspace}";
            } else {
                return $path . "?_workspace={$workspace}";
            }
        }
    }
}