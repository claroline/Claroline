<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use Composer\Json\JsonFile;
use Composer\Repository\InstalledFilesystemRepository;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Service
 * @Tag("twig.extension")
 */
class ComposerExtension extends \Twig_Extension
{
    protected $kernel;

    /**
     * @InjectParams({
     *     "kernel" = @Inject("kernel")
     * })
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->installedRepoFile = $this->kernel->getRootDir().'/../vendor/composer/installed.json';
    }

    public function getFunctions()
    {
        return [
            'get_current_distribution_commit' => new \Twig_Function_Method($this, 'getCurrentDistributionCommit'),
        ];
    }

    public function getName()
    {
        return 'composer_extension';
    }

    public function getCurrentDistributionCommit()
    {
        $repo = new InstalledFilesystemRepository(new JsonFile($this->installedRepoFile));
        $distrib = $repo->findPackage('claroline/distribution', '*');

        return $distrib->getSourceReference();
    }
}
