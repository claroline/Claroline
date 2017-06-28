<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Testing;

use Claroline\CoreBundle\DataFixtures\Required\LoadRequiredFixturesData;
use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoteTestingController extends Controller
{
    /**
     * @EXT\Route(
     *     "/reinstall",
     *     name="claro_test_reinstall",
     * )
     *
     * @return Response
     */
    public function reinstallAction()
    {
        $kernel = $this->container->get('kernel');
        $start = new \DateTime();
        $om = $this->container->get('claroline.persistence.object_manager');

        // purge database
        $purger = new ORMPurger($this->container->get('doctrine.orm.entity_manager'));
        $purger->purge();

        // load required core fixtures
        $fixture = new LoadRequiredFixturesData();
        $referenceRepo = new ReferenceRepository($om);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->container);
        $fixture->load($om);
        $om->startFlushSuite();

        // reset default template
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir').'/../templates/default.zip';
        TemplateBuilder::buildDefault($defaultTemplatePath);

        // install plugin fixtures
        $bundles = $kernel->getBundles();
        $installer = $this->container->get('claroline.plugin.installer');

        foreach ($bundles as $bundle) {
            if ($bundle instanceof DistributionPluginBundle) {
                $installer->install($bundle);
            }
        }

        $om->endFlushSuite();
        $end = new \DateTime();
        $diff = $start->diff($end);
        $duration = $diff->i > 0 ? $diff->i.'m ' : '';
        $duration .= $diff->s.'s';

        return new Response('Platform reinstalled (duration: '.$duration.')');
    }

    /**
     * @EXT\Route(
     *     "/fixture/load",
     *     name="claro_test_load_fixture",
     * )
     * @EXT\Method("POST")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function loadFixtureAction(Request $request)
    {
        $fqcn = $request->request->get('fqcn');

        if (!isset($fqcn) || !class_exists($fqcn)) {
            return new Response('Invalid or missing FQCN parameter', 401);
        }

        $args = $request->request->get('args', []);
        $fixture = new $fqcn($args);
        $om = $this->get('claroline.persistence.object_manager');

        if (method_exists($fixture, 'setContainer')) {
            $fixture->setContainer($this->container);
        }

        $om->startFlushSuite();
        $fixture->load($om);
        $om->endFlushSuite();

        return new Response('Fixture loaded');
    }
}
