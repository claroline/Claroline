<?php

namespace Claroline\AppBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @todo remove the ContainerAware use. It's not always required so let's implementation choose if it want it or not
 */
abstract class AbstractApiController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use RequestDecoderTrait;
}
