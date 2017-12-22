<?php

namespace Claroline\CoreBundle\Routing;

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * @DI\Service("claroline.router", parent="router.default")
 */
class ClaroRouter extends Router
{
    private $host;
    private $scheme;

    public function __construct(
        ContainerInterface $container,
        $resource,
        array $options = [],
        RequestContext $context = null
    ) {
        $ch = $container->get('claroline.config.platform_config_handler');
        $this->host = $ch->getParameter('domain_name');
        $context = $context ?: new RequestContext();
        $sslEnabled = $ch->getParameter('ssl_enabled');
        $this->scheme = $sslEnabled ? 'https' : 'http';
        $this->buildContext($context);

        parent::__construct($container, $resource, $options, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        $this->buildContext($this->context);

        return parent::generate($name, $parameters, $referenceType);
    }

    public function buildContext(RequestContext $context)
    {
        $context->setScheme($this->scheme);

        if ($this->host && trim($this->host) !== '') {
            $context->setHost($this->host);
        }

        parent::setContext($context);
    }
}
