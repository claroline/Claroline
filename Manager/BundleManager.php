<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Claroline\CoreBundle\Persistence\ObjectManager;

/**
 * @Service("claroline.manager.bundle_manager")
 */
class BundleManager
{
    private $om;
    private $bundleRepository;

    /**
     * @InjectParams({
     *      "om" = @Inject("%claroline.persistence.object_manager%"),
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
        $this->bundleRepository = $om->getRepository('ClarolineCoreBundle:Bundle');
    }

    public function getBundle($bundle)
    {
        return $this->bundleRepository->findOneByName($bundle);
    }
}
