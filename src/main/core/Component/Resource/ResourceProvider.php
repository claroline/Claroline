<?php

namespace Claroline\CoreBundle\Component\Resource;

use Claroline\AppBundle\Component\AbstractComponentProvider;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Aggregates all the resources defined in the Claroline app.
 *
 * A resource MUST:
 *   - be declared as a symfony service and tagged with "claroline.component.resource".
 *   - implement the ResourceInterface interface (or the ResourceComponent class).
 */
class ResourceProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredResources,
        private readonly TempFileManager $tempFileManager
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.resource';
    }

    /**
     * Get the list of all the tools injected in the app by the current plugins.
     * It does not contain tools for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredResources;
    }

    public function fromUrl(string $url): ?array
    {
        $urlHandler = null;
        foreach ($this->getRegisteredComponents() as $resourceHandler) {
            if ($resourceHandler instanceof UrlAdapterInterface) {
                // checks if the current resource supports the submitted url
                $support = $resourceHandler->supportsUrl($url);
                if (UrlAdapterInterface::SUPPORTED === $support) {
                    // perfect file match
                    $urlHandler = $resourceHandler;
                    break;
                } elseif (UrlAdapterInterface::SUPPORTED_PARTIAL === $support) {
                    // only partial support, continue searching to find a perfect support if any
                    $urlHandler = $resourceHandler;
                }
            }
        }

        if (!$urlHandler) {
            // the url type is not supported by any enabled resource
            return null;
        }

        return array_merge_recursive([
            'meta' => [
                'type' => $urlHandler::getName(),
            ],
            'url' => $url,
        ], $urlHandler->fromUrl($url) ?? []);
    }

    public function fromFile(UploadedFile $file, ?string $resourceType = null): ?array
    {
        $fileHandler = null;

        if ($resourceType) {
            $resourceHandler = $this->getComponent($resourceType);
            if ($resourceHandler instanceof FileAdapterInterface) {
                // checks if the current resource supports the submitted file
                $support = $resourceHandler->supportsFile($file);
                if (FileAdapterInterface::SUPPORTED === $support) {
                    // perfect file match
                    $fileHandler = $resourceHandler;
                } elseif (FileAdapterInterface::SUPPORTED_PARTIAL === $support) {
                    // only partial support, continue searching to find a perfect support if any
                    $fileHandler = $resourceHandler;
                }
            }
        } else {
            // find the correct resource handler for the submitted file
            foreach ($this->getRegisteredComponents() as $resourceHandler) {
                if ($resourceHandler instanceof FileAdapterInterface) {
                    // checks if the current resource supports the submitted file
                    $support = $resourceHandler->supportsFile($file);
                    if (FileAdapterInterface::SUPPORTED === $support) {
                        // perfect file match
                        $fileHandler = $resourceHandler;
                        break;
                    } elseif (FileAdapterInterface::SUPPORTED_PARTIAL === $support) {
                        // only partial support, continue searching to find a perfect support if any
                        $fileHandler = $resourceHandler;
                    }
                }
            }
        }

        if (!$fileHandler) {
            // the file type is not supported by any enabled resource
            return null;
        }

        // clean filename to generate the resource name
        $extension = pathinfo($file->getClientOriginalName() ?: $file->getFilename(), PATHINFO_EXTENSION);
        $resourceName = str_replace('.'.$extension, '', $file->getClientOriginalName() ?: $file->getFilename());
        $resourceName = str_replace('_', ' ', $resourceName);
        $resourceName = ucfirst($resourceName);

        // move the file in temp to reuse it when the user will create the resource
        $tempName = $this->tempFileManager->copy($file, true);

        return array_merge_recursive([
            'name' => $resourceName,
            'meta' => [
                'type' => $fileHandler::getName(),
                'mimeType' => $file->getMimeType(),
            ],
            'size' => filesize($file),
            'url' => $tempName,
        ], $fileHandler->fromFile($file) ?? []);
    }
}
