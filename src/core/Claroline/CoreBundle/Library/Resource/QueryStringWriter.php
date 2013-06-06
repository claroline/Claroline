<?php

namespace Claroline\CoreBundle\Library\Resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Resource\ModeAccessor;

/**
 * @DI\Service("claroline.resource.query_string_writer")
 *
 * Writes a query string according to parameters passed in the current request,
 * allowing to keep some context information through links and redirections.
 */
class QueryStringWriter
{
    private $request;
    private $modeAccessor;

    /**
     * @DI\InjectParams({
     *     "container"  = @DI\Inject("service_container"),
     *     "accessor"   = @DI\Inject("claroline.resource.mode_accessor")
     * })
     */
    public function __construct(ContainerInterface $container, ModeAccessor $accessor)
    {
        $this->request = $container->isScopeActive('request') ? $container->get('request') : false;
        $this->modeAccessor = $accessor;
    }

    /**
     * Returns a query string retaining the resource parameters passed in the
     * current request.
     *
     * Parameters taken into account are :
     *
     * - "_mode": indicates whether a resource should be rendered within a full page layout (default)
     *            or in a minimal "path" layout (used for activities / learning path)
     * - "_breadcrumbs": path of the directory in which the resource should be displayed
     *
     * @return string
     */
    public function getQueryString()
    {
        if ($this->request) {
            $parameters = array();

            if ($this->modeAccessor->isPathMode()) {
                $parameters['_mode'] = 'path';
            }

            foreach (array('_breadcrumbs') as $parameter) {
                if (null !== $value = $this->request->query->get($parameter)) {
                    $parameters[$parameter] = $value;
                }
            }

            return http_build_query($parameters);
        }

        return '';
    }
}