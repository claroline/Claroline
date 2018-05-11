<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * This file is part of the Pagerfanta package.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\PagerfantaInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyPath;
use WhiteOctober\PagerfantaBundle\Twig\PagerfantaExtension as P;

/**
 * @DI\Service(parent="twig.extension.pagerfanta")
 * @DI\Tag("twig.extension")
 */
class PagerfantaExtension extends P
{
    use ContainerAwareTrait;

    /**
     * Renders a pagerfanta.
     *
     * @param PagerfantaInterface $pagerfanta the pagerfanta
     * @param string              $viewName   the view name
     * @param array               $options    an array of options (optional)
     *
     * @return string the pagerfanta rendered
     */
    public function renderPagerfanta(PagerfantaInterface $pagerfanta, $viewName = null, array $options = [])
    {
        $options = array_replace(
            [
                'routeName' => null,
                'routeParams' => [],
                'pageParameter' => '[page]',
                'queryString' => null,
            ],
            $options
        );

        if (null === $viewName) {
            $viewName = $this->container->getParameter('white_october_pagerfanta.default_view');
        }

        $router = $this->container->get('router');

        if (null === $options['routeName']) {
            $request = $this->container->get('request_stack')->getMasterRequest();

            $options['routeName'] = $request->attributes->get('_route');
            if ('_internal' === $options['routeName']) {
                throw new \Exception('PagerfantaBundle can not guess the route when used in a subrequest');
            }

            $options['routeParams'] = array_merge($request->query->all(), $request->attributes->get('_route_params'));
        }

        $routeName = $options['routeName'];
        $routeParams = $options['routeParams'];
        $pagePropertyPath = new PropertyPath($options['pageParameter']);
        $routeGenerator = function ($page) use ($router, $routeName, $routeParams, $pagePropertyPath, $options) {
            $propertyAccessor = PropertyAccess::getPropertyAccessor();
            $propertyAccessor->setValue($routeParams, $pagePropertyPath, $page);

            $url = $router->generate($routeName, $routeParams);

            if ($options['queryString']) {
                $url .= '?'.$options['queryString'];
            }

            return $url;
        };

        return $this->container->get('white_october_pagerfanta.view_factory')
            ->get($viewName)->render($pagerfanta, $routeGenerator, $options);
    }
}
