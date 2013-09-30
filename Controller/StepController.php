<?php

/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2013 Innovalangues
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   Entity
 * @package    InnovaPathBundle
 * @subpackage PathBundle
 * @author     Innovalangues <contact@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    0.1
 * @link       http://innovalangues.net
 */
namespace Innova\PathBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Innova\PathBundle\Entity\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\Resource;
use Innova\PathBundle\Entity\StepType;
use Innova\PathBundle\Entity\StepWho;
use Innova\PathBundle\Entity\StepWhere;
use Innova\PathBundle\Entity\Step2ResourceNode;
use Innova\PathBundle\Entity\Step2ExcludedResource;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;

/**
 * Class StepController
 *
 * @category   Controller
 * @package    Innova
 * @subpackage PathBundle
 * @author     Innovalangues <contant@innovalangues.net>
 * @copyright  2013 Innovalangues
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.1
 * @link       http://innovalangues.net
*/
class StepController extends Controller
{
     /**
     * Finds and displays a Step entity.
     *
     * @Route("workspace/{workspaceId}/path/{pathId}/step/{stepId}", name="step_show")
     * @Method("GET")
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function showAction($workspaceId, $pathId, $stepId)
    {
        $manager = $this->entityManager();
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        $pathResourceNode = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($pathId);
        $stepResourceNode = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($stepId);

        $step = $manager->getRepository('InnovaPathBundle:Step')->findOneByResourceNode($stepResourceNode->getId());
        $path = $manager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathResourceNode->getId());

        $resources = $step->getResources();

        return array(
            'step' => $step,
            'stepResourceNode' => $stepResourceNode,
            'pathResourceNode' => $pathResourceNode,
            'resources' => $resources,
            'path' => $path,
            'workspace' => $workspace,
        );
    }

    /**
     * entityManager function
     *
     * @return $em
     *
     */
    public function entityManager()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $em = $this->getDoctrine()->getManager();

        return $em;
    }

}
