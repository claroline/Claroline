<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nico
 * Date: 04/07/13
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\WikiBundle\Controller;


use Claroline\CoreBundle\Entity\User;
use Icap\WikiBundle\Entity\Wiki;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Form\SectionType;
use Icap\WikiBundle\Form\EditSectionType;
use Icap\WikiBundle\Form\DeleteSectionType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiController extends Controller{

    /**
     * @Route(
     *      "/{wikiId}",
     *      requirements={"wikiId" = "\d+"},
     *      name="icap_wiki_view"
     * )
     * @ParamConverter("wiki", class="IcapWikiBundle:Wiki", options={"id" = "wikiId"})
     * @Template()
     */
    public function viewAction(Wiki $wiki)
    {
        $this->checkAccess("OPEN", $wiki);
        $isAdmin = $this->isUserGranted("EDIT", $wiki);
        $sectionRepository = $this->get('icap.wiki.section_repository');
        $tree = $sectionRepository->buildSectionTree($wiki, $isAdmin);

        return array(
            'wiki' => $wiki,
            'tree' => $tree,
            'workspace' => $wiki->getResourceNode()->getWorkspace(),
            'isAdmin' => $isAdmin
        );
    }    
}
