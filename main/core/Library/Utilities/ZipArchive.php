<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

class ZipArchive extends \ZipArchive
{
    //I know this is dirty but I was asked to find a solution
    //for ZipArchive special char encoding.
    //It actually works but in this code lies madness.
    public function extractTo($extractPath, $files = null)
    {
        $fs = new FileSystem();
        $ds = DIRECTORY_SEPARATOR;

        for ($i = 0; $i < $this->numFiles; ++$i) {
            $oldName = parent::getNameIndex($i);
            $newName = mb_convert_encoding(
                $this->getNameIndex($i),
                'ISO-8859-1',
                'CP850,UTF-8'
            );

            //we cheat a little because we can't tell wich name the extracted part should have
            //so we put it a directory wich share it's name
            $tmpDir = $extractPath.$ds.'__claro_zip_hack_'.$oldName;
            parent::extractTo($tmpDir, parent::getNameIndex($i));
            //now we move the content of the directory and we put the good name on it.

            foreach ($iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tmpDir,
                    \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item) {
                if ($item->isFile()) {
                    $fs->mkdir(dirname($extractPath.$ds.$oldName));
                    $fs->rename($item->getPathname(), $extractPath.$ds.$oldName);
                }
            }
        }

        //we remove our 'trash here'
        $iterator = new \DirectoryIterator($extractPath);

        foreach ($iterator as $item) {
            if (strpos($item->getFilename(), '_claro_zip_hack')) {
                $fs->rmdir($item->getRealPath(), true);
            }
        }
    }
}
