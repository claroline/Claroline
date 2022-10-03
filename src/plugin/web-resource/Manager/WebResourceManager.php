<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WebResourceManager
{
    /** @var string */
    private $filesDir;
    /** @var string */
    private $uploadDir;
    /** @var ObjectManager */
    private $om;

    private $defaultIndexFiles = [
        'web/SCO_0001/default.html',
        'web/SCO_0001/default.htm',
        'web/index.html',
        'web/index.htm',
        'index.html',
        'index.htm',
        'web/SCO_0001/Default.html',
        'web/SCO_0001/Default.htm',
        'web/Index.html',
        'web/Index.htm',
        'Index.html',
        'Index.htm',
    ];

    public function __construct(
        string $filesDir,
        string $uploadDir,
        ObjectManager $om
    ) {
        $this->filesDir = $filesDir;
        $this->uploadDir = $uploadDir;
        $this->om = $om;
    }

    public function create(UploadedFile $tmpFile, Workspace $workspace): array
    {
        $filesPath = $this->filesDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR;
        $file = new File();
        $fileName = $tmpFile->getClientOriginalName();
        $hash = $this->getHash(pathinfo($fileName, PATHINFO_EXTENSION));
        $size = filesize($tmpFile);
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hash);
        $file->setMimeType('custom/claroline_web_resource');
        $tmpFile->move($filesPath, $hash);
        $this->unzip($hash, $workspace);

        return [
            'hashName' => $hash,
            'size' => $size,
        ];
    }

    /**
     * Try to retrieve root file of the WebResource from the unzipped directory.
     */
    public function guessRootFileFromUnzipped(string $hash): ?string
    {
        // Grab all HTML files from Archive
        $htmlFiles = $this->getHTMLFiles($hash);

        // Only one file
        if (1 === count($htmlFiles)) {
            return array_shift($htmlFiles);
        }

        // Check usual default root files
        foreach ($this->defaultIndexFiles as $file) {
            if (in_array($file, $htmlFiles)) {
                return $file;
            }
        }

        // Unable to find an unique HTML file
        return null;
    }

    /**
     * Checks if a UploadedFile is a zip and contains index.html file.
     */
    public function isZip(UploadedFile $file, Workspace $workspace): bool
    {
        $isZip = false;
        $archive = new \ZipArchive();
        if ('application/zip' === $file->getClientMimeType() || true === $archive->open($file)) {
            // Correct Zip type => check if html root file exists
            $rootFile = $this->guessRootFile($file, $workspace);

            if (!empty($rootFile)) {
                $isZip = true;
            }
        }

        return $isZip;
    }

    /**
     * Unzips files in web directory.
     *
     * @param string $hash The hash name of the resource
     */
    public function unzip(string $hash, Workspace $workspace)
    {
        $filesPath = $this->filesDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR;
        $zipPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR;
        if (!file_exists($zipPath.$hash)) {
            mkdir($zipPath.$hash, 0777, true);
        }

        $archive = new \ZipArchive();
        $archive->open($filesPath.$hash);
        $archive->extractTo($zipPath.$hash);
        $archive->close();
    }

    /**
     * Deletes web resource unzipped files.
     *
     * @param string $dir The path to the directory to delete
     */
    private function unzipDelete(string $dir)
    {
        foreach (glob($dir.'/*') as $file) {
            if (is_dir($file)) {
                $this->unzipDelete($file);
            } else {
                unlink($file);
            }
        }

        rmdir($dir);
    }

    /**
     * Get all HTML files from a zip archive.
     */
    private function getHTMLFiles(string $directory): array
    {
        try {
            $dir = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::NEW_CURRENT_AND_KEY);
            $files = new \RecursiveIteratorIterator($dir);

            $allowedExtensions = ['htm', 'html'];

            $list = [];
            foreach ($files as $file) {
                if (in_array($file->getExtension(), $allowedExtensions)) {
                    // HTML File found
                    $relativePath = str_replace($directory, '', $file->getPathname());
                    $list[] = ltrim($relativePath, '\\/');
                }
            }

            return $list;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Try to retrieve root file of the WebResource from the zip archive.
     */
    private function guessRootFile(UploadedFile $file, Workspace $workspace): ?string
    {
        $zipPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR;

        $archive = new \ZipArchive();
        if (!$archive->open($file)) {
            throw new \Exception('Can not open archive file.');
        }

        // Try to locate usual default HTML files to avoid unzip archive and scan directory tree
        foreach ($this->defaultIndexFiles as $html) {
            if (is_numeric($archive->locateName($html))) {
                return $html;
            }
        }

        // No default index file found => scan archive
        // Extract content into tmp dir
        $tmpDir = $zipPath.'tmp/';
        if (!$tmpDir) {
            mkdir($zipPath.'tmp/', 0777, true);
        }
        $archive->extractTo($tmpDir);
        $archive->close();

        // Search for root file
        $htmlFiles = $this->getHTMLFiles($tmpDir);

        // Remove tmp data
        $this->unzipDelete($tmpDir);

        // Only one file
        if (1 === count($htmlFiles)) {
            return array_shift($htmlFiles);
        }

        return null;
    }

    /**
     * Returns a new hash for a file.
     *
     * @param mixed mixed The extension of the file or an Claroline\CoreBundle\Entity\Resource\File
     */
    private function getHash($mixed): string
    {
        if ($mixed instanceof File) {
            $mixed = pathinfo($mixed->getHashName(), PATHINFO_EXTENSION);
        }

        return Uuid::uuid4()->toString().'.'.$mixed;
    }
}
