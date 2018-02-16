<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\DropzoneBundle\Command;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Criterion;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\Grade;
use Icap\DropzoneBundle\Entity\Dropzone as IcapDropzone;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Converts Icap Dropzone resources to Claroline Dropzone resources.
 */
class ConvertDropzoneCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:dropzone:convert')
            ->setDescription('Converts Dropzone resources from IcapDropzoneBundle to Dropzone resources from ClarolineDropZoneBundle');

        $this->addOption(
            'logs',
            'l',
            InputOption::VALUE_NONE,
            'When set to true, all associated logs are also copied'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conversion = [
            IcapDropzone::MANUAL_STATE_NOT_STARTED => Dropzone::STATE_NOT_STARTED,
            IcapDropzone::MANUAL_STATE_ALLOW_DROP => Dropzone::STATE_ALLOW_DROP,
            IcapDropzone::MANUAL_STATE_FINISHED => Dropzone::STATE_FINISHED,
            IcapDropzone::MANUAL_STATE_PEER_REVIEW => Dropzone::STATE_PEER_REVIEW,
            IcapDropzone::MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW => Dropzone::STATE_ALLOW_DROP_AND_PEER_REVIEW,
            IcapDropzone::AUTO_CLOSED_STATE_WAITING => Dropzone::AUTO_CLOSED_STATE_WAITING,
            IcapDropzone::AUTO_CLOSED_STATE_CLOSED => Dropzone::AUTO_CLOSED_STATE_CLOSED,
            'file' => Document::DOCUMENT_TYPE_FILE,
            'text' => Document::DOCUMENT_TYPE_TEXT,
            'url' => Document::DOCUMENT_TYPE_URL,
            'resource' => Document::DOCUMENT_TYPE_RESOURCE,
        ];

        $om = $this->getContainer()->get('claroline.persistence.object_manager');
        $resourceManager = $this->getContainer()->get('claroline.manager.resource_manager');
        $filesDir = $this->getContainer()->getParameter('claroline.param.files_directory');
        $fileSystem = $this->getContainer()->get('filesystem');
        $icapDropzoneRepo = $om->getRepository('Icap\DropzoneBundle\Entity\Dropzone');
        $ds = DIRECTORY_SEPARATOR;

        $dropzoneType = $resourceManager->getResourceTypeByName('claroline_dropzone');
        $allDropzones = $icapDropzoneRepo->findAll();

        $startDate = new \DateTime();
        $nbResources = 0;
        $nbDrops = 0;
        $nbDocuments = 0;
        $nbFiles = 0;
        $nbFoundFiles = 0;
        $nbLogs = 0;
        $om->startFlushSuite();
        $i = 1;

        foreach ($allDropzones as $dropzone) {
            $node = $dropzone->getResourceNode();

            if ($node->isActive()) {
                $output->writeln('<info>  Starting conversion of resource ['.$node->getName().']...</info>');

                $criteriaMapping = [];
                $currentDate = new \DateTime();

                $output->writeln('<info>      Copying resource node...</info>');

                /* Copies ResourceNode */
                $newNode = new ResourceNode();
                $newNode->setAccesses($node->getAccesses());
                $newNode->setAccessibleFrom($node->getAccessibleFrom());
                $newNode->setAccessibleUntil($node->getAccessibleUntil());
                $newNode->setAuthor($node->getAuthor());
                $newNode->setClass('Claroline\DropZoneBundle\Entity\Dropzone');
                $newNode->setClosable($node->getClosable());
                $newNode->setCloseTarget($node->getCloseTarget());
                $newNode->setCreationDate($node->getCreationDate());
                $newNode->setCreator($node->getCreator());
                $newNode->setDescription($node->getDescription());
                $newNode->setFullscreen($node->getFullscreen());
                $newNode->setIcon($node->getIcon());
                $newNode->setIndex($node->getIndex());
                $newNode->setLicense($node->getLicense());
                $newNode->setMimeType('custom/claroline_dropzone');
                $newNode->setModificationDate($node->getModificationDate());
                $newNode->setName($node->getName());
                $newNode->setParent($node->getParent());
                $newNode->setPathForCreationLog($node->getPathForCreationLog());
                $newNode->setPublished($node->isPublished());
                $newNode->setPublishedToPortal($node->isPublishedToPortal());
                $newNode->setResourceType($dropzoneType);
                $newNode->setWorkspace($node->getWorkspace());

                $output->writeln('<info>      Copying rights for resource node...</info>');

                /* Copies rights */
                foreach ($node->getRights() as $rights) {
                    $newRights = new ResourceRights();
                    $newRights->setResourceNode($newNode);
                    $newRights->setMask($rights->getMask());
                    $newRights->setRole($rights->getRole());
                    $om->persist($newRights);
                }

                $output->writeln('<info>      Updating resource node shortcuts...</info>');

                /* Updates shortcuts */
                foreach ($node->getShortcuts()->toArray() as $shortcut) {
                    $shortcutNode = $shortcut->getResourceNode();
                    $shortcutNode->setMimeType('custom/claroline_dropzone');
                    $shortcutNode->setResourceType($dropzoneType);
                    $om->persist($shortcutNode);

                    $shortcut->setTarget($newNode);
                    $om->persist($shortcut);
                }
                $om->persist($newNode);

                $output->writeln('<info>      Creating new Dropzone resource...</info>');

                /* Copies Dropzone resource */
                $newDropzone = new Dropzone();
                $newDropzone->setResourceNode($newNode);
                $newDropzone->setAutoCloseDropsAtDropEndDate($dropzone->getAutoCloseOpenedDropsWhenTimeIsUp());
                $newDropzone->setAutoCloseState($conversion[$dropzone->getAutoCloseState()]);
                $newDropzone->setCommentInCorrectionEnabled($dropzone->getAllowCommentInCorrection());
                $newDropzone->setCommentInCorrectionForced($dropzone->getForceCommentInCorrection());
                $newDropzone->setCorrectionDenialEnabled($dropzone->getAllowCorrectionDeny());
                $newDropzone->setCorrectionInstruction($dropzone->getCorrectionInstruction());
                $newDropzone->setCriteriaEnabled($dropzone->hasCriteria());
                $newDropzone->setCriteriaTotal($dropzone->getTotalCriteriaColumn());
                $newDropzone->setDisplayCorrectionsToLearners($dropzone->getDiplayCorrectionsToLearners());
                $newDropzone->setDisplayNotationMessageToLearners($dropzone->getDisplayNotationMessageToLearners());
                $newDropzone->setDisplayNotationToLearners($dropzone->getDisplayNotationToLearners());
                $newDropzone->setDropEndDate($dropzone->getEndAllowDrop());
                $newDropzone->setDropStartDate($dropzone->getStartAllowDrop());
                $newDropzone->setEditionState($dropzone->getEditionState());
                $newDropzone->setExpectedCorrectionTotal($dropzone->getExpectedTotalCorrection());
                $newDropzone->setFailMessage($dropzone->getFailMessage());
                $newDropzone->setInstruction($dropzone->getInstruction());
                $newDropzone->setManualPlanning($dropzone->getManualPlanning());
                $newDropzone->setManualState($conversion[$dropzone->getManualState()]);
                $newDropzone->setNotifyOnDrop($dropzone->getNotifyOnDrop());
                $newDropzone->setPeerReview($dropzone->getPeerReview());
                $newDropzone->setReviewEndDate($dropzone->getEndReview());
                $newDropzone->setReviewStartDate($dropzone->getStartReview());
                $newDropzone->setScoreMax(20);
                $newDropzone->setScoreToPass($dropzone->getMinimumScoreToPass());
                $newDropzone->setSuccessMessage($dropzone->getSuccessMessage());
                $allowedDocuments = [];

                if ($dropzone->getAllowRichText()) {
                    $allowedDocuments[] = 'html';
                }
                if ($dropzone->getAllowUpload()) {
                    $allowedDocuments[] = 'file';
                }
                if ($dropzone->getAllowUrl()) {
                    $allowedDocuments[] = 'url';
                }
                if ($dropzone->getAllowWorkspaceResource()) {
                    $allowedDocuments[] = 'resource';
                }
                $newDropzone->setAllowedDocuments($allowedDocuments);

                $output->writeln('<info>      Copying criteria...</info>');

                /* Copies Criteria & creates an array to keep associations */
                foreach ($dropzone->getPeerReviewCriteria()->toArray() as $criterion) {
                    $newCriterion = new Criterion();
                    $newCriterion->setDropzone($newDropzone);
                    $newCriterion->setInstruction($criterion->getInstruction());
                    $om->persist($newCriterion);

                    $criteriaMapping[$criterion->getId()] = $newCriterion;
                }

                $output->writeln('<info>      Copying drops, documents and corrections...</info>');

                /* Copies Drops */
                foreach ($dropzone->getDrops()->toArray() as $drop) {
                    $newDrop = new Drop();
                    $newDrop->setDropzone($newDropzone);
                    $newDrop->setAutoClosedDrop($drop->getAutoClosedDrop());
                    $newDrop->setDropDate($drop->getDropDate());
                    $newDrop->setFinished($drop->getFinished());
                    $newDrop->setNumber($drop->getNumber());
                    $newDrop->setReported($drop->getReported());
                    $newDrop->setUnlockedDrop($drop->isUnlockedDrop());
                    $newDrop->setUnlockedUser($drop->getUnlockedUser());
                    $newDrop->setUser($drop->getUser());

                    /* Copies Documents */
                    foreach ($drop->getDocuments() as $document) {
                        $dropDate = $drop->getDropDate() ? $drop->getDropDate() : $currentDate;
                        $documentResource = $document->getResourceNode();

                        $newDocument = new Document();
                        $newDocument->setDrop($newDrop);
                        $newDocument->setDropDate($dropDate);
                        $newDocument->setType($conversion[$document->getType()]);

                        switch ($conversion[$document->getType()]) {
                            case Document::DOCUMENT_TYPE_FILE:
                                /* Gets file form the File resource & deactivates node at the end */
                                if (!empty($documentResource)) {
                                    $fileResource = $resourceManager->getResourceFromNode($documentResource);

                                    if (!empty($fileResource)) {
                                        $fileName = $fileResource->getHashName();

                                        if ($fileSystem->exists($filesDir.$ds.$fileResource->getHashName())) {
                                            $fileSystem->copy(
                                                $filesDir.$ds.$fileResource->getHashName(),
                                                $filesDir.$ds.'dropzone'.$ds.$newDropzone->getUuid().$ds.$fileName
                                            );
                                            ++$nbFoundFiles;
                                        }
                                        $data = [
                                            'url' => '../files/dropzone'.$ds.$newDropzone->getUuid().$ds.$fileName,
                                            'name' => $documentResource->getName(),
                                        ];
                                        $newDocument->setFile($data);
                                    }
                                    $documentResource->setActive(false);
                                    $om->persist($documentResource);
                                    ++$nbFiles;
                                }
                                break;
                            case Document::DOCUMENT_TYPE_TEXT:
                                /* Gets the content of the Text resource & deactivates node at the end */
                                if (!empty($documentResource)) {
                                    $textResource = $resourceManager->getResourceFromNode($documentResource);

                                    if (!empty($textResource)) {
                                        $newDocument->setContent($textResource->getContent());
                                    }
                                    $documentResource->setActive(false);
                                    $om->persist($documentResource);
                                }
                                break;
                            case Document::DOCUMENT_TYPE_URL:
                                $newDocument->setUrl($document->getUrl());
                                break;
                            case Document::DOCUMENT_TYPE_RESOURCE:
                                /* For resource type the parent is remplaced by the new node */
                                if (!empty($documentResource)) {
                                    $documentResource->setParent($newNode);
                                    $om->persist($documentResource);
                                    $newDocument->setResource($documentResource);
                                }
                                break;
                        }
                        $newDocument->setUser($drop->getUser());
                        $om->persist($newDocument);
                        ++$nbDocuments;
                    }

                    /* Copies Corrections */
                    foreach ($drop->getCorrections() as $correction) {
                        $newCorrection = new Correction();
                        $newCorrection->setDrop($newDrop);
                        $newCorrection->setComment($correction->getComment());
                        $newCorrection->setCorrectionDenied($correction->getCorrectionDenied());
                        $newCorrection->setCorrectionDeniedComment($correction->getCorrectionDeniedComment());
                        $newCorrection->setEditable($correction->getEditable());
                        $newCorrection->setEndDate($correction->getEndDate());
                        $newCorrection->setFinished($correction->getFinished());
                        $newCorrection->setLastEditionDate($correction->getLastOpenDate());
                        $newCorrection->setReported($correction->getReporter());
                        $newCorrection->setReportedComment($correction->getReportComment());
                        $newCorrection->setScore($correction->getTotalGrade());
                        $newCorrection->setStartDate($correction->getStartDate());
                        $newCorrection->setUser($correction->getUser());
                        $newCorrection->setValid($correction->getValid());

                        /* Copying Grades */
                        foreach ($correction->getGrades()->toArray() as $grade) {
                            $newGrade = new Grade();
                            $newGrade->setCriterion($criteriaMapping[$grade->getCriterion()->getId()]);
                            $newGrade->setCorrection($newCorrection);
                            $newGrade->setValue($grade->getValue());
                            $om->persist($newGrade);
                        }

                        $om->persist($newCorrection);
                    }

                    $om->persist($newDrop);
                    ++$nbDrops;
                }

                $withLogs = $input->getOption('logs');

                if ($withLogs) {
                    $output->writeln('<info>      Updating logs...</info>');

                    /* Updates logs */
                    foreach ($node->getLogs()->toArray() as $log) {
                        $log->setResourceNode($newNode);
                        $log->setResourceType($dropzoneType);
                        $om->persist($log);
                        ++$nbLogs;
                    }
                }

                $om->persist($newDropzone);
                ++$nbResources;

                $output->writeln('<info>      Soft deleting old resource and associated hidden directory...</info>');

                /* Soft deletes old resource node and old dropzone hidden directory */
                $hiddenDirectory = $dropzone->getHiddenDirectory();

                if (!empty($hiddenDirectory)) {
                    $hiddenDirectory->setActive(false);
                    $om->persist($hiddenDirectory);
                }
                $node->setActive(false);
                $om->persist($node);

                $output->writeln('<info>  Conversion of resource ['.$node->getName().'] is finished.</info>');

                if ($i % 20 === 0) {
                    $output->writeln('<info>  Flushing...</info>');
                    $om->forceFlush();
                }
                ++$i;
            }
        }
        $om->endFlushSuite();

        $timeDiff = $startDate->diff(new \DateTime());
        $hours = ($timeDiff->days * 24) + $timeDiff->h;
        $minutes = $timeDiff->i;
        $seconds = $timeDiff->s;
        $output->writeln("<info>  Execution time : $hours hours $minutes minutes $seconds seconds</info>");
        $output->writeln("<info>  Number of resources : $nbResources</info>");
        $output->writeln("<info>  Number of drops : $nbDrops</info>");
        $output->writeln("<info>  Number of documents : $nbDocuments</info>");
        $output->writeln("<info>  Number of files : $nbFiles</info>");
        $output->writeln("<info>  Number of found files : $nbFoundFiles</info>");
        $output->writeln("<info>  Number of logs : $nbLogs</info>");
    }
}
