<?php
namespace Innova\CollecticielBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;
use Innova\CollecticielBundle\Event\Log\LogDocumentCreateEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentDeleteEvent;
use Innova\CollecticielBundle\Event\Log\LogDocumentOpenEvent;
use Innova\CollecticielBundle\Form\DocumentDeleteType;
use Innova\CollecticielBundle\Form\DocumentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends DropzoneBaseController
{
    private function getDropZoneHiddenDirectory(Dropzone $dropzone)
    {
        $em = $this->getDoctrine()->getManager();
        $hiddenDirectory = $dropzone->getHiddenDirectory();

        if ($hiddenDirectory === null) {
            $hiddenDirectory = new Directory();
            $name = $this->get('translator')->trans(
                'Hidden folder for "%dropzoneName%"',
                array('%dropzoneName%' => $dropzone->getResourceNode()->getName()),
                'innova_collecticiel'
            );
            $hiddenDirectory->setName($name);

            $role = $this
                ->getDoctrine()
                ->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($dropzone->getResourceNode()->getWorkspace());
            $resourceManager = $this->get('claroline.manager.resource_manager');
            $resourceManager->create(
                $hiddenDirectory,
                $resourceManager->getResourceTypeByName('directory'),
                $dropzone->getResourceNode()->getCreator(),
                $dropzone->getResourceNode()->getWorkspace(),
                $dropzone->getResourceNode()->getParent(),
                null,
                array(
                    'ROLE_WS_MANAGER' => array('open' => true, 'export' => true, 'create' => array(),
                        'role' => $role)
                )
            );

            $dropzone->setHiddenDirectory($hiddenDirectory->getResourceNode());
            $em->persist($dropzone);
            $em->flush();
        }

        return $dropzone->getHiddenDirectory();
    }

    private function getDropHiddenDirectory(Dropzone $dropzone, Drop $drop)
    {
        $em = $this->getDoctrine()->getManager();
        $hiddenDropDirectory = $drop->getHiddenDirectory();

        if ($hiddenDropDirectory == null) {
            $hiddenDropDirectory = new Directory();
            // slugify user name
            $slugify = new Slugify();

            $user = $drop->getUser();
            $str = $user->getFirstName() . "-" . $user->getLastName();
            $str = $slugify->slugify($str, ' ');

            $name = $this->get('translator')->trans('Copy n°%number%', array('%number%' => $drop->getNumber()), 'innova_collecticiel');
            $name .= " - " . $str;
            $hiddenDropDirectory->setName($name);

            $parent = $this->getDropZoneHiddenDirectory($dropzone);
            $role = $this
                ->getDoctrine()
                ->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($dropzone->getResourceNode()->getWorkspace());

            $resourceManager = $this->get('claroline.manager.resource_manager');
            $resourceManager->create(
                $hiddenDropDirectory,
                $resourceManager->getResourceTypeByName('directory'),
                $parent->getCreator(),
                $parent->getWorkspace(),
                $parent,
                null,
                array(
                    'ROLE_WS_MANAGER' => array('open' => true, 'export' => true, 'create' => array(),
                        'role' => $role)
                )
            );

            $drop->setHiddenDirectory($hiddenDropDirectory->getResourceNode());
            $em->persist($drop);
            $em->flush();
        }

        return $drop->getHiddenDirectory();
    }

    private function createFile(Dropzone $dropzone, Drop $drop, $tmpFile)
    {
        $em = $this->getDoctrine()->getManager();
        $parent = $this->getDropHiddenDirectory($dropzone, $drop);

        $file = new File();
        $fileName = $tmpFile->getClientOriginalName();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getClientMimeType();
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() . "." . $extension;
        $tmpFile->move($this->container->getParameter('claroline.param.files_directory'), $hashName);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        $resourceManager = $this->get('claroline.manager.resource_manager');
        $resourceManager->create(
            $file,
            $resourceManager->getResourceTypeByName('file'),
            $dropzone->getResourceNode()->getCreator(),
            $dropzone->getResourceNode()->getWorkspace(),
            $parent
        );
        $em->flush();

        return $file->getResourceNode();
    }

    private function createText(Dropzone $dropzone, Drop $drop, $richText)
    {
        $em = $this->getDoctrine()->getManager();
        $parent = $this->getDropHiddenDirectory($dropzone, $drop);

        $revision = new Revision();
        $revision->setContent($richText);
        $revision->setUser($drop->getUser());
        $text = new Text();
        $text->setName($this->get('translator')->trans('Free text', array(), 'innova_collecticiel'));
        $revision->setText($text);
        $em->persist($text);
        $em->persist($revision);

        $resourceManager = $this->get('claroline.manager.resource_manager');
        $resourceManager->create(
            $text,
            $resourceManager->getResourceTypeByName('text'),
            $dropzone->getResourceNode()->getCreator(),
            $dropzone->getResourceNode()->getWorkspace(),
            $parent
        );
        $em->flush();

        return $text->getResourceNode();
    }

    private function createResource(Dropzone $dropzone, Drop $drop, $resourceId)
    {
        if ($resourceId == null) {
            throw new \ErrorException();
        }
        $em = $this->getDoctrine()->getManager();
        $parent = $this->getDropHiddenDirectory($dropzone, $drop);
        $node = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($resourceId);
        $resourceManager = $this->get('claroline.manager.resource_manager');
        $copy = $resourceManager->copy(
            $node,
            $parent,
            $dropzone->getResourceNode()->getCreator()
        )->getResourceNode();
        $em->flush();

        return $copy;
    }

    private function createDocument(Dropzone $dropzone, Drop $drop, $form, $documentType)
    {
        $document = new Document();
        $document->setType($documentType);

        $node = null;
        if ($documentType == 'url') {
            $data = $form->getData();
            $url = $data['document'];
            $document->setUrl($url);
        } else if ($documentType == 'file') {
            $file = $form['document'];
            $node = $this->createFile($dropzone, $drop, $file->getData());
        } else if ($documentType == 'text') {
            $data = $form->getData();
            $node = $this->createText($dropzone, $drop, $data['document']);
        } else if ($documentType == 'resource') {
            $data = $form->getData();
            $node = $this->createResource($dropzone, $drop, $data['document']);
        } else {
            throw new \ErrorException();
        }
        $document->setResourceNode($node);

        // #19. Ajout de la valorisation de la Date. InnovaERV.
        $document->setDocumentDate(new \DateTime());
        
        $sender = $this->get('security.token_storage')->getToken()->getUser();
        
        if ($sender->getId() !== $drop->getUser()->getId()) {
            $document->setValidate(true);
        }

        $document->setDrop($drop);
        $document->setSender($sender);

        $em = $this->getDoctrine()->getManager();
        $em->persist($document);
        $em->flush();

        $event = new LogDocumentCreateEvent($dropzone, $drop, $document);
        $this->dispatch($event);
    }

    /**
     * @Route(
     *      "/{resourceId}/document/{documentType}/{dropId}",
     *      name="innova_collecticiel_document",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "documentType" = "url|file|resource|text"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="InnovaCollecticielBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function documentAction($dropzone, $documentType, $drop)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        $formType = null;
        if ($documentType == 'url') {
            if (!$dropzone->getAllowUrl()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'file') {
            if (!$dropzone->getAllowUpload()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'resource') {
            if (!$dropzone->getAllowWorkspaceResource()) {
                throw new AccessDeniedException();
            }
        } else if ($documentType == 'text') {
            if (!$dropzone->getAllowRichText()) {
                throw new AccessDeniedException();
            }
        }
        $form = $this->createForm(new DocumentType(), null, array('documentType' => $documentType));

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());

            if ($form->isValid()) {
                $this->createDocument($dropzone, $drop, $form, $documentType);

                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_drop',
                        array(
                            'resourceId' => $dropzone->getId()
                        )
                    )
                );
            }
        }

        $view = 'InnovaCollecticielBundle:Document:document.html.twig';
        if ($this->getRequest()->isXMLHttpRequest()) {
            $view = 'InnovaCollecticielBundle:Document:documentInline.html.twig';
        }

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'drop' => $drop,
                'documentType' => $documentType,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/delete/document/{dropId}/{documentId}",
     *      name="innova_collecticiel_delete_document",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "documentId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="InnovaCollecticielBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function deleteDocumentAction(Dropzone $dropzone, $user, Drop $drop, Document $document)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        if ($drop->getId() != $document->getDrop()->getId()) {
            throw new \HttpInvalidParamException();
        }

        if ($drop->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new DocumentDeleteType(), $document);

        if ($this->getRequest()->isMethod('POST')) {
            $form->handleRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->remove($document);
                $em->flush();

                $event = new LogDocumentDeleteEvent($dropzone, $drop, $document);
                $this->dispatch($event);

                return $this->redirect(
                    $this->generateUrl(
                        'innova_collecticiel_drop',
                        array(
                            'resourceId' => $dropzone->getId()
                        )
                    )
                );
            }
        }

        $view = 'InnovaCollecticielBundle:Document:deleteDocument.html.twig';
        if ($this->getRequest()->isXMLHttpRequest()) {
            $view = 'InnovaCollecticielBundle:Document:deleteDocumentModal.html.twig';
        }

        return $this->render(
            $view,
            array(
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'drop' => $drop,
                'document' => $document,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/open/resource/{documentId}",
     *      name="innova_collecticiel_open_resource",
     *      requirements={"resourceId" = "\d+", "documentId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="InnovaCollecticielBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function openResourceAction(Dropzone $dropzone, Document $document, $user)
    {
        $this->get('innova.manager.dropzone_voter')->isAllowToOpen($dropzone);

        if ($document->getType() == 'url') {
            return $this->redirect($document->getUrl());
        } elseif (
            $document->getType() == 'text'
            or $document->getType() == 'resource'
            or $document->getType() == 'file'
        ) {
            /** Issue #27 "il se produit un plantage au niveau de "temporary_access_resource_manager" InnovaERV */
            $this->get('innova.temporary_access_resource_manager')->addTemporaryAccess($document->getResourceNode(), $user);

            $event = new LogDocumentOpenEvent($dropzone, $document->getDrop(), $document);
            $this->dispatch($event);

            if ($document->getResourceNode()->getResourceType()->getName() == 'file') {
                return $this->redirect(
                    $this->generateUrl('claro_resource_download') . '?ids[]=' . $document->getResourceNode()->getId()
                );
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'claro_resource_open',
                        array(
                            'resourceType' => $document->getResourceNode()->getResourceType()->getName(),
                            'node' => $document->getResourceNode()->getId()
                        )
                    )
                );
            }
        }
    }

    /**
     * @Route(
     *      "/document/{documentId}",
     *      name="innova_collecticiel_validate_document",
     *      requirements={"documentId" = "\d+"},
     *      options={"expose"=true}
     * )
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function ajaxValidateDocumentAction(Document $document) {

        // Appel pour accés base         
        $em = $this->getDoctrine()->getManager();

        // Recherche en base des données du document à mettre à jour
        $doc = $this->getDoctrine()->getRepository('InnovaCollecticielBundle:Document')->find($document->getId());
        
        // Mise à jour du booléen de Validation de false à true
        $doc->setvalidate(true);

        // Mise à jour de la base de données
        $em->persist($doc);
        $em->flush();

        // Ajout afin d'afficher la partie du code avec "Demande transmise"
        $template = $this->get("templating")->
        render('InnovaCollecticielBundle:Document:documentIsValidate.html.twig',
                array('document' => $document)
               );

        // Retour du template actualisé à l'Ajax et non plus du Json.
        return new Response($template);
    }

    /**
     * @Route(
     *      "/undocument/{documentId}",
     *      name="innova_collecticiel_unvalidate_document",
     *      requirements={"documentId" = "\d+"},
     *      options={"expose"=true}
     * )
     * @ParamConverter("document", class="InnovaCollecticielBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function ajaxUnvalidateDocumentAction(Document $document) {
        
        // Appel pour accés base
        $em = $this->getDoctrine()->getManager();

        // Recherche en base des données du document à mettre à jour
        $doc = $this->getDoctrine()->getRepository('InnovaCollecticielBundle:Document')->find($document->getId());
        
        // Mise à jour du booléen de Validation de true à false
        $doc->setvalidate(false);

        // Mise à jour de la base de données
        $em->persist($doc);
        $em->flush();

        // Ajout afin d'afficher la partie du code avec "Demande transmise"
        $template = $this->get("templating")->
        render('InnovaCollecticielBundle:Document:documentIsValidate.html.twig',
                array('document' => $document)
               );

        // Retour du template actualisé à l'Ajax et non plus du Json.
        return new Response($template);
    }
}
