<?php

namespace Icap\DropzoneBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Cocur\Slugify\Slugify;
use Icap\DropzoneBundle\Entity\Document;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;
use Icap\DropzoneBundle\Event\Log\LogDocumentCreateEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentDeleteEvent;
use Icap\DropzoneBundle\Event\Log\LogDocumentOpenEvent;
use Icap\DropzoneBundle\Form\DocumentDeleteType;
use Icap\DropzoneBundle\Form\DocumentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
                ['%dropzoneName%' => $dropzone->getResourceNode()->getName()],
                'icap_dropzone'
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
                [
                    'ROLE_WS_MANAGER' => ['open' => true, 'export' => true, 'create' => [],
                        'role' => $role, ],
                ]
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

        if ($hiddenDropDirectory === null) {
            $hiddenDropDirectory = new Directory();
            // slugify user name
            $slugify = new Slugify();

            $user = $drop->getUser();
            $username = $user->getUsername();
            $directoryName = $user->getLastName().' '.$user->getFirstName();
            $directoryName = $slugify->slugify($directoryName, ' ');

            // Add username to ensure directory name is unique even if some users share the same name
            $directoryName .= ' - '.$username;
            $hiddenDropDirectory->setName($directoryName);

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
                [
                    'ROLE_WS_MANAGER' => ['open' => true, 'export' => true, 'create' => [],
                        'role' => $role, ],
                ]
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
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid().'.'.$extension;
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
        $text->setName($this->get('translator')->trans('Free text', [], 'icap_dropzone'));
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
        if ($resourceId === null) {
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
        if ($documentType === 'url') {
            $data = $form->getData();
            $url = $data['document'];
            $document->setUrl($url);
        } elseif ($documentType === 'file') {
            $file = $form['document'];
            $node = $this->createFile($dropzone, $drop, $file->getData());
        } elseif ($documentType === 'text') {
            $data = $form->getData();
            $node = $this->createText($dropzone, $drop, $data['document']);
        } elseif ($documentType === 'resource') {
            $data = $form->getData();
            $node = $this->createResource($dropzone, $drop, $data['document']);
        } else {
            throw new \ErrorException();
        }
        $document->setResourceNode($node);
        $document->setDrop($drop);

        $em = $this->getDoctrine()->getManager();
        $em->persist($document);
        $em->flush();

        $event = new LogDocumentCreateEvent($dropzone, $drop, $document);
        $this->dispatch($event);
    }

    /**
     * @Route(
     *      "/{resourceId}/document/{documentType}/{dropId}",
     *      name="icap_dropzone_document",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "documentType" = "url|file|resource|text"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @Template()
     */
    public function documentAction(Request $request, $dropzone, $documentType, $drop)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);

        if ($documentType === 'url') {
            if (!$dropzone->getAllowUrl()) {
                throw new AccessDeniedException();
            }
        } elseif ($documentType === 'file') {
            if (!$dropzone->getAllowUpload()) {
                throw new AccessDeniedException();
            }
        } elseif ($documentType === 'resource') {
            if (!$dropzone->getAllowWorkspaceResource()) {
                throw new AccessDeniedException();
            }
        } elseif ($documentType === 'text') {
            if (!$dropzone->getAllowRichText()) {
                throw new AccessDeniedException();
            }
        }
        $form = $this->createForm(new DocumentType(), null, ['documentType' => $documentType]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->createDocument($dropzone, $drop, $form, $documentType);

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drop',
                        [
                            'resourceId' => $dropzone->getId(),
                        ]
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Document:document.html.twig';
        if ($request->isXMLHttpRequest()) {
            $view = 'IcapDropzoneBundle:Document:documentInline.html.twig';
        }

        return $this->render(
            $view,
            [
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'drop' => $drop,
                'documentType' => $documentType,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/delete/document/{dropId}/{documentId}",
     *      name="icap_dropzone_delete_document",
     *      requirements={"resourceId" = "\d+", "dropId" = "\d+", "documentId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("drop", class="IcapDropzoneBundle:Drop", options={"id" = "dropId"})
     * @ParamConverter("document", class="IcapDropzoneBundle:Document", options={"id" = "documentId"})
     * @Template()
     */
    public function deleteDocumentAction(Request $request, Dropzone $dropzone, $user, Drop $drop, Document $document)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);

        if ($drop->getId() !== $document->getDrop()->getId()) {
            throw new \HttpInvalidParamException();
        }

        if ($drop->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new DocumentDeleteType(), $document);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->remove($document);
                $em->flush();

                $event = new LogDocumentDeleteEvent($dropzone, $drop, $document);
                $this->dispatch($event);

                return $this->redirect(
                    $this->generateUrl(
                        'icap_dropzone_drop',
                        [
                            'resourceId' => $dropzone->getId(),
                        ]
                    )
                );
            }
        }

        $view = 'IcapDropzoneBundle:Document:deleteDocument.html.twig';
        if ($request->isXMLHttpRequest()) {
            $view = 'IcapDropzoneBundle:Document:deleteDocumentModal.html.twig';
        }

        return $this->render(
            $view,
            [
                'workspace' => $dropzone->getResourceNode()->getWorkspace(),
                '_resource' => $dropzone,
                'dropzone' => $dropzone,
                'drop' => $drop,
                'document' => $document,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route(
     *      "/{resourceId}/open/resource/{documentId}",
     *      name="icap_dropzone_open_resource",
     *      requirements={"resourceId" = "\d+", "documentId" = "\d+"}
     * )
     * @ParamConverter("dropzone", class="IcapDropzoneBundle:Dropzone", options={"id" = "resourceId"})
     * @ParamConverter("document", class="IcapDropzoneBundle:Document", options={"id" = "documentId"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     */
    public function openResourceAction(Dropzone $dropzone, Document $document, $user)
    {
        $this->get('icap.manager.dropzone_voter')->isAllowToOpen($dropzone);

        if ($document->getType() === 'url') {
            return $this->redirect($document->getUrl());
        } elseif (
            $document->getType() === 'text'
            || $document->getType() === 'resource'
            || $document->getType() === 'file'
        ) {
            $this->get('claroline.temporary_access_resource_manager')->addTemporaryAccess($document->getResourceNode(), $user);

            $event = new LogDocumentOpenEvent($dropzone, $document->getDrop(), $document);
            $this->dispatch($event);

            if ($document->getResourceNode()->getResourceType()->getName() === 'file') {
                return $this->redirect(
                    $this->generateUrl('claro_resource_download').'?ids[]='.$document->getResourceNode()->getId()
                );
            } else {
                return $this->redirect(
                    $this->generateUrl(
                        'claro_resource_open',
                        [
                            'resourceType' => $document->getResourceNode()->getResourceType()->getName(),
                            'node' => $document->getResourceNode()->getId(),
                        ]
                    )
                );
            }
        }
    }
}
