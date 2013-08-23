<?php
namespace Claroline\CoreBundle\Controller;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Manager\HomeManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * @TODO doc
 */
class HomeController
{
    private $manager;
    private $request;
    private $security;
    private $templating;
    private $homeService;

    /**
     * @InjectParams({
     *     "manager"        = @Inject("claroline.manager.home_manager"),
     *     "security"       = @Inject("security.context"),
     *     "request"        = @Inject("request"),
     *     "templating"     = @Inject("templating"),
     *     "homeService"    = @Inject("claroline.common.home_service")
     * })
     */
    public function __construct(HomeManager $manager, Request $request, $security, $templating, $homeService)
    {
        $this->manager = $manager;
        $this->request = $request;
        $this->security = $security;
        $this->templating = $templating;
        $this->homeService = $homeService;
    }

    /**
     * Get content by id
     *
     * @Route(
     *     "/content/{content}/{type}/{father}",
     *     requirements={"content" = "\d+"},
     *     name="claroline_get_content_by_id_and_type",
     *     defaults={"type" = "home", "father" = null}
     * )
     *
     * @ParamConverter("content", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "content"})
     * @ParamConverter("father", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "father"})
     * @ParamConverter("type", class = "ClarolineCoreBundle:Home\Type", options = {"mapping" : {"type": "name"}})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentAction(Content $content, Type $type, Content $father = null)
    {
        return $this->render(
            'ClarolineCoreBundle:Home/types:'.(is_object($type) ? $type->getName() : 'home' ).'.html.twig',
            $this->manager->getContent($content, $type, $father),
            true
        );
    }

    /**
     * Render the home page of the platform
     *
     * @Route("/type/{type}", name="claro_get_content_by_type")
     * @Route("/", name="claro_index", defaults={"type" = "home"})
     * @Template("ClarolineCoreBundle:Home:home.html.twig")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function homeAction($type)
    {
        return array(
            'region' => $this->renderRegions($this->manager->getRegionContents()),
            'content' => $this->typeAction($type)->getContent()
        );
    }

    /**
     * Render the layout of contents by type.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeAction($type, $father = null, $region = null)
    {
        $layout = $this->manager->contentLayout($type, $father, $region);

        if ($layout) {

            return $this->render('ClarolineCoreBundle:Home:layout.html.twig', $this->renderContent($layout));
        }

        return $this->render('ClarolineCoreBundle:Home:error.html.twig', array('path' => $type));
    }

    /**
     * Render the page of types administration.
     *
     * @Route("/types", name="claroline_types_manager")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @Template("ClarolineCoreBundle:Home:home.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typesAction()
    {
        $types = $this->manager->getTypes();

        return array(
            'region' => $this->renderRegions($this->manager->getRegionContents()),
            'content' => $this->render(
                'ClarolineCoreBundle:Home:types.html.twig',
                array('types' => $types)
            )->getContent()
        );
    }

    /**
     * Render the page of the creator box.
     *
     * @Route("/content/creator/{type}/{id}/{father}", name="claroline_content_creator", defaults={"father" = null})
     *
     * @param string $type The type of the content to create.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creatorAction($type, $id = null, $content = null, $father = null)
    {
        //cant use @Secure(roles="ROLE_ADMIN") annotation beacause this method is called in anonymous mode
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->render(
                'ClarolineCoreBundle:Home/types:'.$type.'.creator.twig',
                $this->manager->getCreator($type, $id, $content, $father),
                true
            );
        }

        return new Response(); //return void and not an exeption
    }

    /**
     * Render the page of the menu.
     *
     * @param string $id   The id of the content.
     * @param string $size The size (content-12) of the content.
     * @param string $type The type of the content.
     *
     * @Template("ClarolineCoreBundle:Home:menu.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction($id, $size, $type, $father = null)
    {
        return $this->manager->getMenu($id, $size, $type, $father);
    }

    /**
     * Render the HTML of the menu of sizes of the contents.
     *
     * @param string $id   The id of the content.
     * @param string $size The size (content-12) of the content.
     * @param string $type The type of the content.
     *
     * @Route("/content/size/{id}/{size}/{type}", name="claroline_content_size")
     *
     * @Template("ClarolineCoreBundle:Home:sizes.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sizeAction($id, $size, $type)
    {
        return array('id' => $id, 'size' => $size, 'type' => $type);
    }

    /**
     * Render the HTML of a content generated by an external url with Open Grap meta tags
     *
     * @Route("/content/graph", name="claroline_content_graph")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function graphAction()
    {
        $graph = $this->manager->getGraph($this->request->get('generated_content_url'));

        if (isset($graph['type'])) {
            return $this->render(
                'ClarolineCoreBundle:Home/graph:'.$graph['type'].'.html.twig',
                array('content' => $graph),
                true
            );
        }

        return new Response('false');
    }

    /**
     * Render the HTML of the regions.
     *
     * @Route("/content/region/{id}", name="claroline_region")
     *
     * @param string $id The id of the content.
     *
     * @Template("ClarolineCoreBundle:Home:regions.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regionAction($id)
    {
        return array('id' => $id);
    }

    /**
     * Render the HTML of the content.
     *
     * @return array
     */
    public function renderContent($content)
    {
        $tmp = ' '; // void in case of not yet content

        if (isset($content['content']) and isset($content['type']) and is_array($content['content'])) {
            foreach ($content['content'] as $content) {
                $tmp .= $this->render(
                    'ClarolineCoreBundle:Home/types:'.$content['type'].'.html.twig', $content, true
                )->getContent();
            }
            $content['content'] = $tmp;
        }

        return $content;
    }

    /**
     * Render the HTML of the regions.
     *
     * @return string
     */
    public function renderRegions($regions)
    {
        $tmp = array();

        foreach ($regions as $name => $region) {
            $tmp[$name] = '';

            foreach ($region as $variables) {
                $tmp[$name] .= $this->render(
                    'ClarolineCoreBundle:Home/types:'.$variables['type'].'.html.twig', $variables, true
                )->getContent();
            }
        }

        return $tmp;
    }

    /**
     * Create new content by POST method. This is used by ajax.
     * The response is the id of the new content in success, otherwise the response is the false word in a string.
     *
     * @Route("/content/create", name="claroline_content_create")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        if ($id = $this->manager->createContent(
            $this->request->get('title'),
            $this->request->get('text'),
            $this->request->get('generated'),
            $this->request->get('type'),
            $this->request->get('father')
        )) {
            return new Response($id);
        }

        return new Response('false'); //useful in ajax
    }

    /**
     * Update a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @Route("/content/update/{content}", name="claroline_content_update")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @ParamConverter("content", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "content"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($content)
    {
        try {
            $this->manager->UpdateContent(
                $content,
                $this->request->get('title'),
                $this->request->get('text'),
                $this->request->get('generated'),
                $this->request->get('size'),
                $this->request->get('type')
            );

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * Reorder contents in types. This method is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @param string $type The type of the content.
     * @param string $a    The id of the content 1.
     * @param string $b    The id of the content 2.
     *
     * @Route("/content/reorder/{type}/{a}/{b}", requirements={"a" = "\d+"}, name="claroline_content_reorder")
     *
     * @Secure(roles="ROLE_ADMIN")
     *
     * @ParamConverter("type", class = "ClarolineCoreBundle:Home\Type", options = {"mapping": {"type": "name"}})
     *
     * @ParamConverter("a", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "a"})
     * @ParamConverter("b", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "b"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reorderAction($type, $a, Content $b = null)
    {
        try {
            $this->manager->reorderContent($type, $a, $b);

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * Delete a content by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @Route("/content/delete/{content}", name="claroline_content_delete")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @ParamConverter("content", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "content"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction($content)
    {
        try {
            $this->manager->deleteContent($content);

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * Verify if a type exist.
     *
     * @Route("/content/typeexist/{name}", name="claroline_content_typeexist")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typeExistAction($name)
    {
        if ($this->manager->typeExist($name)) {
            return new Response('true');
        }

        return new Response('false');
    }

    /**
     * Create a type by POST method. This is used by ajax.
     * The response is a template of the type in success, otherwise false.
     *
     * @Route("/content/createtype/{name}", name="claroline_content_createtype")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @Template("ClarolineCoreBundle:Home:type.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createTypeAction($name)
    {
        try {
            return array('type' => $this->manager->createType($name));
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * Delete a type by POST method. This is used by ajax.
     * The response is the word true in a string in success, otherwise false.
     *
     * @Route("/content/deletetype/{type}", name="claroline_content_deletetype")
     * @Secure(roles="ROLE_ADMIN")
     *
     * @ParamConverter("type", class = "ClarolineCoreBundle:Home\Type", options = {"id" = "type"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deletetypeAction($type)
    {
        try {
            $this->manager->deleteType($type);

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * Put a content into a region in front page as left, right, footer. This is sueful for menus.
     *
     * @Route("/region/{region}/{content}", requirements={"content" = "\d+"}, name="claroline_content_to_region")
     *
     * @ParamConverter("region", class = "ClarolineCoreBundle:Home\Region", options = {"mapping": {"region": "name"}})
     * @ParamConverter("content", class = "ClarolineCoreBundle:Home\Content", options = {"id" = "content"})
     *
     * @Secure(roles="ROLE_ADMIN")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contentToRegionAction($region, $content)
    {
        try {
            $this->manager->contentToRegion($region, $content);

            return new Response('true');
        } catch (\Exeption $e) {
            return new Response('false'); //useful in ajax
        }
    }

    /**
     * Extends templating render
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function render($template, $variables, $default = false)
    {
        if ($default) {
            $template = $this->homeService->defaultTemplate($template);
        }

        return new Response($this->templating->render($template, $variables));
    }
}
