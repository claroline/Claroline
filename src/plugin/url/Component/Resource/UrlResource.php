<?php

namespace HeVinci\UrlBundle\Component\Resource;

use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Component\Resource\UrlAdapterInterface;

class UrlResource extends ResourceComponent implements UrlAdapterInterface
{
    public static function getName(): string
    {
        return 'hevinci_url';
    }

    public function supportsUrl(string $url): int
    {
        return UrlAdapterInterface::SUPPORTED_PARTIAL;
    }

    public function fromUrl(string $url): ?array
    {
        return [
            'name' => trim(str_replace(['http://', 'https://'], '', $url), '/'),
            'raw' => $url,
        ];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
