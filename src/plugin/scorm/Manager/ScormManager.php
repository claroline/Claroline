<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Exception\InvalidScormArchiveException;
use Claroline\ScormBundle\Library\ScormLib;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ScormManager
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ScormLib */
    private $scormLib;
    /** @var string */
    private $uploadDir;
    /** @var string */
    private $filesDir;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        ScormLib $scormLib,
        string $filesDir,
        string $uploadDir
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->scormLib = $scormLib;
        $this->filesDir = $filesDir;
        $this->uploadDir = $uploadDir;
    }

    public function uploadScormArchive(Workspace $workspace, File $file)
    {
        // Checks if it is a valid scorm archive
        $zip = new \ZipArchive();
        $openValue = $zip->open($file);

        $isScormArchive = (true === $openValue) && $zip->getStream('imsmanifest.xml');

        $zip->close();

        if (!$isScormArchive) {
            throw new InvalidScormArchiveException('invalid_scorm_archive_message');
        } else {
            return $this->generateScorm($workspace, $file);
        }
    }

    /**
     * @deprecated It must use Crud instead
     */
    public function updateScorm(Scorm $scorm, $data)
    {
        $newScorm = $this->serializer->deserialize($data, $scorm);
        $this->om->persist($newScorm);
        $this->om->flush();

        return $this->serializer->serialize($newScorm);
    }

    /**
     * Unzip a given ZIP file into the web resources directory.
     *
     * @param string $hashName name of the destination directory
     */
    public function unzipScormArchive(Workspace $workspace, File $file, $hashName)
    {
        $zip = new \ZipArchive();
        $zip->open($file);
        $ds = DIRECTORY_SEPARATOR;
        $destinationDir = $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName;

        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        $zip->extractTo($destinationDir);
        $zip->close();
    }

    private function generateScorm(Workspace $workspace, File $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString().'.zip';
        $scormData = $this->parseScormArchive($file);
        $this->unzipScormArchive($workspace, $file, $hashName);
        // Move Scorm archive in the files directory
        $finalFile = $file->move($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid(), $hashName);

        return [
            'name' => $hashName, // to follow standard file data format
            'hashName' => $hashName,
            'type' => $finalFile->getMimeType(),
            'version' => $scormData['version'],
            'scos' => $scormData['scos'],
        ];
    }

    public function copy(Scorm $scorm, Workspace $workspaceDest)
    {
        $workspace = $scorm->getResourceNode()->getWorkspace();

        $hashName = $scorm->getHashName();

        if ($workspace->getId() !== $workspaceDest->getId()) {
            $filesystem = new Filesystem();
            $ds = DIRECTORY_SEPARATOR;
            /* Copies archive file & unzipped files */
            if ($filesystem->exists($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName)) {
                $filesystem->copy(
                    $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName,
                    $this->filesDir.$ds.'scorm'.$ds.$workspaceDest->getUuid().$ds.$hashName
                );
            }
            if ($filesystem->exists($this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName)) {
                $filesystem->mirror(
                    $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName,
                    $this->uploadDir.$ds.'scorm'.$ds.$workspaceDest->getUuid().$ds.$hashName
                );
            }
        }
    }

    private function parseScormArchive(File $file)
    {
        $data = [];
        $contents = '';
        $zip = new \ZipArchive();

        $zip->open($file);
        $stream = $zip->getStream('imsmanifest.xml');

        while (!feof($stream)) {
            $contents .= fread($stream, 2);
        }
        $dom = new \DOMDocument();

        if (!$dom->loadXML($contents)) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }

        $scormVersionElements = $dom->getElementsByTagName('schemaversion');

        if (1 === $scormVersionElements->length) {
            switch ($scormVersionElements->item(0)->textContent) {
                case '1.2':
                    $data['version'] = Scorm::SCORM_12;
                    break;
                case 'CAM 1.3':
                case '2004 3rd Edition':
                case '2004 4th Edition':
                    $data['version'] = Scorm::SCORM_2004;
                    break;
                default:
                    throw new InvalidScormArchiveException('invalid_scorm_version_message');
            }
        } else {
            throw new InvalidScormArchiveException('invalid_scorm_version_message');
        }

        $scos = $this->scormLib->parseOrganizationsNode($dom);

        if (0 >= count($scos)) {
            throw new InvalidScormArchiveException('no_sco_in_scorm_archive_message');
        }
        $data['scos'] = array_map(function (Sco $sco) {
            return $this->serializer->serialize($sco);
        }, $scos);

        return $data;
    }
}
