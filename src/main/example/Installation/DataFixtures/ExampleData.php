<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Installation\DataFixtures;

use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\ExampleBundle\Entity\Example;
use Claroline\InstallationBundle\Fixtures\PreInstallInterface;
use Claroline\InstallationBundle\Fixtures\PreUpdateInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ExampleData extends AbstractFixture implements PreInstallInterface, PreUpdateInterface, ContainerAwareInterface
{
    private TempFileManager $tempManager;
    private FileManager $fileManager;
    private string $projectDir;

    public function setContainer(ContainerInterface $container = null): void
    {
        $this->tempManager = $container->get(TempFileManager::class);
        $this->fileManager = $container->get(FileManager::class);
        $this->projectDir = $container->getParameter('kernel.project_dir');
    }

    /**
     * Loads some example data into the platform.
     */
    public function load(ObjectManager $manager): void
    {
        // create sample thumbnail & poster
        $poster = $this->createSampleFile('poster.jpg');
        $thumbnail = $this->createSampleFile('thumbnail.jpg');

        for ($i = 0; $i < 60; ++$i) {
            $example = new Example();
            $example->setName("Example {$i}");
            $example->setThumbnail($thumbnail->getUrl());
            $example->setPoster($poster->getUrl());
            $example->setDescription("Description for the example entity {$i}");

            $manager->persist($example);
        }

        $manager->flush();
    }

    private function createSampleFile(string $filename): PublicFile
    {
        $samplePath = implode(DIRECTORY_SEPARATOR, [$this->projectDir, 'src', 'main', 'example', 'Resources', 'samples']);

        // the creation will move the file in the files dir, we cannot directly use the source file
        // otherwise it will be removed from the samples dir
        $file = new File($this->tempManager->copy(
            new File($samplePath.DIRECTORY_SEPARATOR.$filename)
        ));

        return $this->fileManager->createFile($file, $filename);
    }
}
