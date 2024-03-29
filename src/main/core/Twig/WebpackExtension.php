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

use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class WebpackExtension extends AbstractExtension
{
    private array $assetCache = [];

    public function __construct(
        private readonly AssetExtension $assetExtension,
        private readonly string $environment,
        private readonly string $projectDir
    ) {
    }

    public function getFunctions(): array
    {
        return [
            'hotAsset' => new TwigFunction('hotAsset', [$this, 'hotAsset']),
        ];
    }

    public function getName(): string
    {
        return 'webpack_extension';
    }

    /**
     * Returns the URL of an asset managed by webpack. The final URL will depend
     * on the environment and the version hash generated by webpack.
     */
    public function hotAsset(string $path, bool $hot = true): string
    {
        $assets = $this->getWebpackAssets();
        $assetName = pathinfo($path, PATHINFO_FILENAME);

        if (!isset($assets[$assetName])) {
            $assetNames = implode("\n", array_keys($assets));

            throw new \Exception("Cannot find asset '{$assetName}' in webpack stats. Found:\n{$assetNames})");
        }

        if ('dev' === $this->environment && $hot) {
            // for dev serve fill from webpack-dev-server
            return 'http://localhost:8080/dist/'.$assets[$assetName]['js'];
        }

        // otherwise serve static generated files
        return $this->assetExtension->getAssetUrl(
            'dist/'.$assets[$assetName]['js']
        );
    }

    private function getWebpackAssets(): array
    {
        if (!$this->assetCache) {
            $assetFile = 'prod'; // for prod and test envs
            if ('dev' === $this->environment) {
                $assetFile = 'dev';
            }

            $assetFile = "{$this->projectDir}/webpack-{$assetFile}.json";

            if (!file_exists($assetFile)) {
                throw new \Exception(sprintf('Cannot find webpack generated assets file(s). Make sure you have ran webpack with assets-webpack-plugin enabled'));
            }

            $this->assetCache = json_decode(file_get_contents($assetFile), true);
        }

        return $this->assetCache;
    }
}
