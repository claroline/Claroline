<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Widget;

use Claroline\CoreBundle\Entity\Widget\SimpleTextConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Form\SimpleTextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SimpleTextController extends Controller
{
    /**
     * @EXT\Route(
     *     "/simple_text_update/config/{widget}",
     *     name="claro_simple_text_update_config"
     * )
     * @EXT\Method("POST")
     */
    public function updateSimpleTextWidgetConfig(WidgetInstance $widget, Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('edit', $widget)) {
            throw new AccessDeniedException();
        }

        $simpleTextConfig = $this->get('claroline.manager.simple_text_manager')->getTextConfig($widget);
        //wtf !
        $keys = array_keys($request->request->all());
        $id = array_pop($keys);
        $form = $this->get('form.factory')->create(new SimpleTextType($id));
        $form->bind($this->getRequest());

        if ($form->isValid()) {
            $formDatas = $form->get('content')->getData();
            $content = is_null($formDatas) ? '' : $formDatas;

            if ($simpleTextConfig) {
                $simpleTextConfig->setContent($content);
            } else {
                $simpleTextConfig = new SimpleTextConfig();
                $simpleTextConfig->setWidgetInstance($widget);
                $simpleTextConfig->setContent($content);
            }
        } else {
            $simpleTextConfig = new SimpleTextConfig();
            $simpleTextConfig->setWidgetInstance($widget);
            $errorForm = $this->container->get('form.factory')
                ->create(new SimpleTextType('widget_text_'.rand(0, 1000000000)), $simpleTextConfig);
            $errorForm->setData($form->getData());
            $children = $form->getIterator();
            $errorChildren = $errorForm->getIterator();

            foreach ($children as $key => $child) {
                $errors = $child->getErrors();

                foreach ($errors as $error) {
                    $errorChildren[$key]->addError($error);
                }
            }

            return $this->render(
                'ClarolineCoreBundle:Widget:SimpleText\configure.html.twig',
                [
                    'form' => $errorForm->createView(),
                    'config' => $widget,
                ]
            );
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($simpleTextConfig);
        $em->flush();

        return new Response('success', 204);
    }
}
