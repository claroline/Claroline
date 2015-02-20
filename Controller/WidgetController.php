<?php

namespace Icap\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Icap\BlogBundle\Entity\WidgetBlog;
use Icap\BlogBundle\Entity\WidgetBlogList;
use Icap\BlogBundle\Listener\BlogListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WidgetController extends Controller
{
    /**
     * @return \Icap\BlogBundle\Manager\WidgetManager
     */
    public function getWidgetManager()
    {
        return $this->get('icap_blog.manager.widget');
    }

    /**
     * @Route("/icap_blog/widget/list/{id}/config", name="icap_blog_widget_list_configure", requirements={"id" = "\d+"})
     * @Method("POST")
     */
    public function updateWidgetBlogList(Request $request, WidgetInstance $widgetInstance)
    {
        if (!$this->get('security.context')->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $originalWidgetListBlogs = $this->getWidgetManager()->getWidgetListBlogs($widgetInstance);
        $originalWidgetListBlogs = new ArrayCollection($originalWidgetListBlogs);

        $widgetBlogList = new WidgetBlogList();
        $widgetBlogList->setWidgetListBlogs($originalWidgetListBlogs);

        /** @var Form $form */
        $form = $this->container->get('form.factory')->create($this->get('icap_blog.form.widget_list'), $widgetBlogList);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            $widgetListBlogs = $widgetBlogList->getWidgetListBlogs();

            foreach ($widgetListBlogs as $widgetListBlog) {
                if ($originalWidgetListBlogs->contains($widgetListBlog)) {
                    $originalWidgetListBlogs->removeElement($widgetListBlog);
                }
                else {
                    $widgetListBlog->setWidgetInstance($widgetInstance);
                    $entityManager->persist($widgetListBlog);
                }
            }

            foreach ($originalWidgetListBlogs as $originalWidgetListBlog) {
                $entityManager->remove($originalWidgetListBlog);
            }

            $entityManager->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->render(
            'IcapBlogBundle:widget:listConfigure.html.twig',
            array(
                'form'           => $form->createView(),
                'widgetInstance' => $widgetInstance
            )
        );
    }
    /**
     * @Route("/icap_blog/widget/blog/{id}/config", name="icap_blog_widget_blog_configure", requirements={"id" = "\d+"})
     * @Method("POST")
     */
    public function updateWidgetBlog(Request $request, WidgetInstance $widgetInstance)
    {
        if (!$this->get('security.context')->isGranted('edit', $widgetInstance)) {
            throw new AccessDeniedException();
        }

        $resourceNode = $this->getWidgetManager()->getResourceNode($widgetInstance);

        $widgetBlog = new WidgetBlog();
        $widgetBlog->setResourceNode($resourceNode);

        /** @var Form $form */
        $form = $this->get('form.factory')->create($this->get('icap_blog.form.widget_blog'), $widgetBlog);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $widgetBlog->setWidgetInstance($widgetInstance);
            $entityManager->persist($widgetBlog);
            $entityManager->flush();

            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->render(
            'IcapBlogBundle:widget:blogConfigure.html.twig',
            array(
                'form'           => $form->createView(),
                'widgetInstance' => $widgetInstance
            )
        );
    }
}
