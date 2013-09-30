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
 * Class PathController
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
class PathController extends Controller
{
    /**
     * fromDesktopAction function
     *
     * @return array response
     *
     * @Route(
     *     "/",
     *     name = "innova_path_from_desktop",
     *     options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle::path_desktop.html.twig")
     */
    public function fromDesktopAction()
    {
        return array();
    }

    /**
     * deployAction function
     *
     * @return array workspace / OK
     *
     * @Route(
     *     "/innova_path_deploy",
     *     name = "innova_path_deploy"
     * )
     * @Method("POST")
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     */
    public function deployAction()
    {
        $manager = $this->entityManager();

        // Récupération vars HTTP
        $pathId = $this->get('request')->request->get('path-id');
        $path = $manager->getRepository('InnovaPathBundle:Path')->findOneByResourceNode($pathId);

        // On récupère la liste des steps avant modification pour supprimer ceux qui ne sont plus utilisés. TO DO : suppression
        $steps = $manager->getRepository('InnovaPathBundle:Step')->findByPath($path->getId());
        // initialisation array() de steps à ne pas supprimer. Sera rempli dans la function JSONParser
        $stepsToNotDelete = array();

        //todo - lister les liens resources2step pour supprimer ceux inutilisés.

        // JSON string to Object - Récupération des childrens de la racine
        $json = json_decode($path->getPath());
        $json_root_steps = $json->steps;

        // Récupération Workspace courant et la resource root
        $workspaceId = $this->get('request')->request->get('workspace-id');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // Récupération utilisateur courant.
        $user = $this->get('security.context')->getToken()->getUser();

        // création du dossier _paths s'il existe pas.
        if (!$pathsDirectory = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths")) {
            $pathsDirectory = new ResourceNode();
            $pathsDirectory->setName("_paths");
            $pathsDirectory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
            $pathsDirectory->setCreator($user);
            $pathsDirectory->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
            $root = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
            $pathsDirectory->setWorkspace($workspace);
            $pathsDirectory->setParent($root);
            $pathsDirectory->setMimeType("custom/directory");
            $pathsDirectory->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));

            $manager->persist($pathsDirectory);
            $manager->flush();
        }

        // lancement récursion
        $this->JSONParser($json_root_steps, $user, $workspace, $pathsDirectory, null, 0, $path, $stepsToNotDelete);

        // On nettoie la base.
        foreach ($steps as $step) {
           if (!in_array($step->getResourceNode()->getId(),$stepsToNotDelete)) {
                $step2ressources = $manager->getRepository('InnovaPathBundle:Step2ResourceNode')->findByStep($step->getId());
                foreach ($step2ressources as $step2ressource) {
                    $manager->remove($step2ressource);
                }
                $step2excludedRessources = $manager->getRepository('InnovaPathBundle:Step2ExcludedResource')->findByStep($step->getId());
                foreach ($step2excludedRessources as $step2excludedRessource) {
                    $manager->remove($step2excludedRessource);
                }

                $manager->remove($step->getResourceNode());
            }
        }

        // Mise à jour des resourceNodeId dans la base.
        $json = json_encode($json);
        $path->setPath($json);

        $manager->flush();

        return array('workspace' => $workspace, 'deployed' => "Parcours déployé.");
    }


    /**
     * private _jsonParser function
     *
     * @param is_object($steps)          $steps          step of activity
     * @param is_object($user)           $user           user of activity
     * @param is_object($workspace)      $workspace      workspace of activity
     * @param is_object($pathsDirectory) $pathsDirectory pathsDirectory of activity
     * @param is_object($parent)         $parent         parent of activity
     * @param is_object($order)          $order          order of activity
     * @param is_object($path)           $path           path of activity
     *
     * @return array
     *
     */
    private function JSONParser($steps, $user, $workspace, $pathsDirectory, $parent, $order, $path, &$stepsToNotDelete)
    {
        $manager = $this->entityManager();
        $rm = $this->resourceManager();

        foreach ($steps as $step) {
            $order++;

            // CLARO_STEP MANAGEMENT
            if ($step->resourceId == null) {
                $resourceNode = new ResourceNode();
                $resourceNode->setClass("Innova\PathBundle\Entity\Step");
                $resourceNode->setCreator($user);
                $resourceNode->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("step"));
                $resourceNode->setWorkspace($workspace);
                $resourceNode->setParent($pathsDirectory);
                $resourceNode->setMimeType("");
                $resourceNode->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));

                $currentStep = new Step();
                $currentStep->setResourceNode($resourceNode);
                $currentStep->setPath($path);
            } else {
                $resourceNode = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($step->resourceId);
                $currentStep = $manager->getRepository('InnovaPathBundle:Step')->findOneByResourceNode($step->resourceId);
            }

            // CLARO_STEP UPDATE
            $resourceNode->setName($step->name);
            $manager->persist($resourceNode);
            $manager->flush($resourceNode);

            // JSON_STEP UPDATE
            $step->resourceId = $resourceNode->getId();

            // STEPSTONODELETE ARRAY UPDATE
            $stepsToNotDelete[] = $resourceNode->getId();

            // CLARO STEP ATTRIBUTES UPDATE
            $currentStep->setStepOrder($order);
            $stepType = $manager->getRepository('InnovaPathBundle:StepType')->findOneById($step->type);
            $currentStep->setStepType($stepType);
            $stepWho = $manager->getRepository('InnovaPathBundle:StepWho')->findOneById($step->who);
            $currentStep->setStepWho($stepWho);
            $stepWhere = $manager->getRepository('InnovaPathBundle:StepWhere')->findOneById($step->where);
            $parent = $manager->getRepository('InnovaPathBundle:Step')->findOneById($parent);
            $currentStep->setParent($parent);
            $currentStep->setStepWhere($stepWhere);
            $currentStep->setDuration(new \DateTime("00-00-00 ".intval($step->durationHours).":".intval($step->durationMinutes).":00"));
            $currentStep->setExpanded($step->expanded);
            $currentStep->setWithTutor($step->withTutor);
            $currentStep->setWithComputer($step->withComputer);
            $currentStep->setInstructions($step->instructions);

            $manager->persist($currentStep);

            // STEP'S RESOURCES MANAGEMENT
            $resourceOrder = 0;
            foreach ($step->resources as $resource) {
                $resourceOrder++;
                // juste pour les tests. cette condition ne sera plus nécessaire après
                if ($resource->resourceId == null) {
                    $step2ressourceNode = new Step2ResourceNode();
                } else {
                    $step2ressourceNode = $manager->getRepository('InnovaPathBundle:Step2ResourceNode')->findOneByResourceNode($resource->resourceId);
                }
                $step2ressourceNode->setResourceNode($manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($resource->resourceId));
                $step2ressourceNode->setStep($currentStep);
                $step2ressourceNode->setPropagated($resource->propagateToChildren);
                $step2ressourceNode->setResourceOrder($resourceOrder);
                $manager->persist($step2ressourceNode);
            }

            // STEP'S EXCLUDED RESOURCES MANAGEMENT
            foreach ($step->excludedResources as $excludedResource) {
                $resourceNodeToExclude = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneById($excludedResource);
                if (!$alreadyExcluded = $manager->getRepository('InnovaPathBundle:Step2ExcludedResource')
                                                ->findOneBy(array('step' => $currentStep, 'resourceNode' => $resourceNodeToExclude)))
                {
                    $step2excludedResource = new Step2ExcludedResource();
                    $step2excludedResource->setResourceNode($resourceNodeToExclude);
                    $step2excludedResource->setStep($currentStep);
                    $manager->persist($step2excludedResource);
                }
            }

            /*
            // TO DO : GESTION DES DROITS
            $right1 = new ResourceRights();
            $right1->setRole($manager->getRepository('ClarolineCoreBundle:Role')->findOneById(3));
            $right1->setResourceNode($resourceNode);
            $manager->persist($right1);
            */
            $manager->flush();

            // récursivité sur les enfants possibles.
            $this->JSONParser($step->children, $user, $workspace, $pathsDirectory, $currentStep->getId(), 0, $path, $stepsToNotDelete);
        }

        $manager->flush();
    }

    /**
     * fromWorkspaceAction function
     *
     * @return array workspace / paths
     *
     * @Route(
     *     "/",
     *     name = "innova_path_from_workspace"
     * )
     *
     * @Template("InnovaPathBundle::path_workspace.html.twig")
     *
     */
    public function fromWorkspaceAction()
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');

        $id = $this->get('request')->query->get('id');

        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($id);

        $resourceType = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('path');

        $paths = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findByWorkspaceAndResourceType($workspace, $resourceType);

        return array('workspace' => $workspace, 'paths' => $paths);
    }

    /**
     * @Route(
     *      "workspace/{workspaceId}/path/editor/{pathId}",
     *      name = "innova_path_editor",
     *      defaults={"pathId"= null},
     *      options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle:Editor:main.html.twig")
     */
    public function editorAction($workspaceId, $pathId = null)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        return array('workspace' => $workspace);
    }


    /**
     * @Route(
     *      "workspace/{workspaceId}/path/player/{pathId}",
     *      name = "innova_path_player",
     *      defaults={"pathId"= null},
     *      options = {"expose"=true}
     * )
     *
     * @Template("InnovaPathBundle:Player:main.html.twig")
     */
    public function PlayerAction($workspaceId, $pathId = null)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        return array('workspace' => $workspace);
    }


    /**
     * @Route(
     *     "/paths",
     *     name = "innova_path_get_paths",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathsAction()
    {
        $em = $this->entityManager();

        $results = $em->getRepository('InnovaPathBundle:Path')->findAll();

        $paths = array();

        foreach ($results as $result) {
            $path = new \stdClass();
            $path->id = $result->getId();
            $path->path = json_decode($result->getPath());

            $paths[] = $path;
        }

        return new JsonResponse($paths);
    }

    /**
     * getPathsAction function
     *
     * @param string $path path of activity
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/path/{id}",
     *     name = "innova_path_get_path",
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     */
    public function getPathAction(Path $path)
    {
        $newPath = json_decode($path->getPath());
        $newPath->id = $path->getId();

        return new JsonResponse($newPath);
    }

    /**
     * addPathAction function
     *
     * @return Response($new_path->getId()
     *
    * @Route(
    *     "/path/add",
    *     name = "innova_path_add_path",
    *     options = {"expose"=true}
    * )
    * @Method("POST")
    *
    */
    public function addPathAction()
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');

        // Récupération utilisateur courant.
        $user = $this->get('security.context')->getToken()->getUser();
        $workspaceId = $this->get('request')->request->get('workspaceId');
        $workspace = $manager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->findOneById($workspaceId);

        // création du dossier _paths s'il existe pas.
        if (!$pathsDirectory = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findOneByName("_paths")) {
            $pathsDirectory = new ResourceNode();
            $pathsDirectory->setName("_paths");
            $pathsDirectory->setClass("Claroline\CoreBundle\Entity\Resource\Directory");
            $pathsDirectory->setCreator($user);
            $pathsDirectory->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneById(2));
            $root = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->findWorkspaceRoot($workspace);
            $pathsDirectory->setWorkspace($workspace);
            $pathsDirectory->setParent($root);
            $pathsDirectory->setMimeType("custom/directory");
            $pathsDirectory->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(7));

            $manager->persist($pathsDirectory);
            $manager->flush();
        }

        $resourceNode = new ResourceNode();
        $resourceNode->setClass("Innova\PathBundle\Entity\Path");
        $resourceNode->setCreator($user);
        $resourceNode->setResourceType($manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName("path"));
        $resourceNode->setWorkspace($workspace);
        $resourceNode->setParent($pathsDirectory);
        $resourceNode->setMimeType("");
        $resourceNode->setIcon($manager->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')->findOneById(1));
        $resourceNode->setName('Path');

        $pathName = $this->get('request')->request->get('pathName');
        $content = $this->get('request')->request->get('path');

        $new_path = new Path;
        $new_path->setPath($content);
        $resourceNode->setName($pathName);
        $new_path->setResourceNode($resourceNode);

        $manager->persist($resourceNode);
        $manager->persist($new_path);
        $manager->flush();

        return new Response(
            $new_path->getId()
        );
    }

    /**
     * editPathAction function
     *
     * @param string $path path of activity
     *
     * @return Response($new_path->getId()
     *
    * @Route(
    *     "/path/edit/{id}",
    *     name = "innova_path_edit_path",
    *     options = {"expose"=true}
    * )
    * @Method("PUT")
    *
    */
    public function editPathAction(Path $path)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');

        $resourceNode = $path->getResourceNode();
        $resourceNode->setName($this->get('request')->request->get('pathName'));
        $manager->persist($resourceNode);

        $content = $this->get('request')->request->get('path');
        $path->setPath($content);
        $manager->persist($path);
        $manager->flush();

        return new Response(
            $path->getId()
        );
    }

    /**
     * deletePathAction function
     *
     * @param string $path path of activity
     *
     * @return OK
     *
     * @Route(
     *     "/path/delete/{id}",
     *     name = "innova_path_delete_path",
     *     options = {"expose"=true}
     * )
     * @Method("DELETE")
     *
     */
    public function deletePathAction(Path $path)
    {
        $em = $this->entityManager();

        $em->remove($path->getResourceNode());
        $em->flush();

        return new Response("ok");
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

    /**
     * resourceManager function
     *
     * @return $rm
     *
     */
    public function resourceManager()
    {
        $rm = $this->get('claroline.manager.resource_manager');

        return $rm;
    }

}
