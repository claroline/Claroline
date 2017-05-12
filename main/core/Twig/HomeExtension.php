<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;

/**
 * @DI\Service("claroline.twig.home_extension")
 * @DI\Tag("twig.extension")
 */
class HomeExtension extends \Twig_Extension
{
    protected $container;
    protected $kernel;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(KernelInterface $kernel, $container)
    {
        $this->kernel = $kernel;
        $this->container = $container;
    }

    /**
     * Get filters of the service.
     *
     * @return \Twig_Filter_Method
     */
    public function getFilters()
    {
        return [
            'timeAgo' => new \Twig_Filter_Method($this, 'timeAgo'),
            'homeLink' => new \Twig_Filter_Method($this, 'homeLink'),
            'activeLink' => new \Twig_Filter_Method($this, 'activeLink'),
            'activeRoute' => new \Twig_Filter_Method($this, 'activeRoute'),
            'compareRoute' => new \Twig_Filter_Method($this, 'compareRoute'),
            'autoLink' => new \Twig_Filter_Method($this, 'autoLink'),
        ];
    }

    public function getFunctions()
    {
        return [
            'isDesktop' => new \Twig_Function_Method($this, 'isDesktop'),
            'asset_exists' => new \Twig_Function_Method($this, 'assetExists'),
        ];
    }

    /**
     * Get the elapsed time since $start to right now, with a transChoice() for translation in plural or singular.
     *
     * @param \DateTime $start The initial time
     *
     * @return \String
     *
     *                 @see Symfony\Component\Translation\Translator
     */
    public function timeAgo($start)
    {
        $end = new \DateTime('now');
        $translator = $this->container->get('translator');
        $interval = $start->diff($end);
        $formats = ['%Y', '%m', '%W', '%d', '%H', '%i', '%s'];

        $translation['singular'] = [
            '%Y' => $translator->trans('year', [], 'platform'),
            '%m' => $translator->trans('month', [], 'platform'),
            '%W' => $translator->trans('week', [], 'platform'),
            '%d' => $translator->trans('day', [], 'platform'),
            '%H' => $translator->trans('hour', [], 'platform'),
            '%i' => $translator->trans('minute', [], 'platform'),
            '%s' => $translator->trans('second', [], 'platform'),
        ];

        $translation['plural'] = [
            '%Y' => $translator->trans('years', [], 'platform'),
            '%m' => $translator->trans('months', [], 'platform'),
            '%W' => $translator->trans('weeks', [], 'platform'),
            '%d' => $translator->trans('days', [], 'platform'),
            '%H' => $translator->trans('hours', [], 'platform'),
            '%i' => $translator->trans('minutes', [], 'platform'),
            '%s' => $translator->trans('seconds', [], 'platform'),
        ];

        foreach ($formats as $format) {
            if ($format === '%W') {
                $i = round($interval->format('%d') / 8); //fix for week that does not exist in DataInterval obj
            } else {
                $i = ltrim($interval->format($format), '0');
            }

            if ($i > 0) {
                $unit = (int) $i === 1 ? $translation['singular'][$format] : $translation['plural'][$format];

                return $this->container->get('translator')->transChoice(
                    'time_ago',
                    $i,
                    ['%count%' => $i, '%unit%' => $unit],
                    'platform'
                );
            }
        }

        //?? why seconds ago
        return $this->container->get('translator')->transChoice(
            'seconds_ago',
            1,
            ['%count%' => 1],
            'home'
        );
    }

    /**
     * Check if a link is local or external.
     */
    public function homeLink($link)
    {
        if (!(strpos($link, 'http://') === 0 ||
            strpos($link, 'https://') === 0 ||
            strpos($link, 'ftp://') === 0 ||
            strpos($link, 'www.') === 0)
        ) {
            $home = $this->container->get('router')->generate('claro_index').$link;

            $home = str_replace('//', '/', $home);

            return $home;
        }

        return $link;
    }

    /**
     * Return active if a given link match to the path info.
     */
    public function activeLink($link)
    {
        $pathinfo = $this->getPathInfo();
        if (($pathinfo && '/'.$pathinfo === $link) || (!$pathinfo && $link === '/')) {
            return ' active'; //the white space is nedded
        }

        return '';
    }

    /**
     * Compare a route with master request route.
     * Usefull in sub-views because there we can not use app.request.get('_route').
     *
     * Example: {% if "claro_get_content_by_type" | activeRoute({'type': 'home'}) %}true{% endif %}
     *
     * @param $route The name of the route
     * @param $params One or more params of the route
     *
     * @return true if the routes match
     */
    public function activeRoute($route, $params = null)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();

        if ($request instanceof Request && $request->get('_route') === $route) {
            if (is_array($params) && count(array_intersect_assoc($request->get('_route_params'), $params)) <= 0) {
                return false;
            }

            return true;
        }
    }

    /**
     * Compare a given link and look if is is inside the the path ifo and start at 0 position.
     */
    public function compareRoute($link, $return = " class='active'")
    {
        $pathinfo = $this->getPathInfo();
        if ($pathinfo && strpos('/'.$pathinfo, $link) === 0) {
            return $return;
        }

        return '';
    }

    /**
     * Find links in a text and made it clickable.
     */
    public function autoLink($text)
    {
        $rexProtocol = '(https?://)?';
        $rexDomain = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
        $rexPort = '(:[0-9]{1,5})?';
        $rexPath = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
        $rexQuery = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
        $rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';

        $text = preg_replace_callback(
            "&\\b$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$))&",
            function ($match) {
                // Prepend http:// if no protocol specified
                $completeUrl = $match[1] ? $match[0] : "http://{$match[0]}";

                return '<a href="'.$completeUrl.'" target="_blank">'
                    .$match[2].$match[3].$match[4].'</a>';
            },
            htmlspecialchars($text)
        );

        return $text;
    }

    /**
     * Check if you come from desktop or workspace.
     */
    public function isDesktop()
    {
        if ($this->container->get('session')->get('isDesktop')) {
            return true;
        }

        return false;
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'home_extension';
    }

    public function assetExists($path)
    {
        $webRoot = realpath($this->kernel->getRootDir().'/../web/');
        $toCheck = realpath($webRoot.$path);

        if (!is_file($toCheck)) {
            return false;
        }

        // check if file is well contained in web/ directory (prevents ../ in paths)
        if (strncmp($webRoot, $toCheck, strlen($webRoot)) !== 0) {
            return false;
        }

        return true;
    }

    private function getPathInfo()
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $router = $this->container->get('router');

        if ($request instanceof Request && $router instanceof Router) {
            $index = $router->generate('claro_index');
            try {
                $current = $router->generate($request->get('_route'), $request->get('_route_params'));

                return str_replace($index, '', $current);
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}
