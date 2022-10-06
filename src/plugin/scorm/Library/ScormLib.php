<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Library;

use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Exception\InvalidScormArchiveException;

class ScormLib
{
    /**
     * Looks for the organization to use.
     *
     * @return array of Sco
     *
     * @throws InvalidScormArchiveException If a default organization
     *                                      is defined and not found
     */
    public function parseOrganizationsNode(\DOMDocument $dom)
    {
        $organizationsList = $dom->getElementsByTagName('organizations');
        $resources = $dom->getElementsByTagName('resource');

        if ($organizationsList->length > 0) {
            $organizations = $organizationsList->item(0);
            $organization = $organizations->firstChild;

            if (!is_null($organizations->attributes)
                && !is_null($organizations->attributes->getNamedItem('default'))) {
                $defaultOrganization = $organizations->attributes->getNamedItem('default')->nodeValue;
            } else {
                $defaultOrganization = null;
            }
            // No default organization is defined
            if (is_null($defaultOrganization)) {
                while (!is_null($organization)
                    && 'organization' !== $organization->nodeName) {
                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {
                    return $this->parseResourceNodes($resources);
                }
            }
            // A default organization is defined
            // Look for it
            else {
                while (!is_null($organization)
                    && ('organization' !== $organization->nodeName
                        || is_null($organization->attributes->getNamedItem('identifier'))
                        || $organization->attributes->getNamedItem('identifier')->nodeValue !== $defaultOrganization)) {
                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {
                    throw new InvalidScormArchiveException('default_organization_not_found_message');
                }
            }

            return $this->parseItemNodes($organization, $resources);
        } else {
            throw new InvalidScormArchiveException('no_organization_found_message');
        }
    }

    /**
     * Creates defined structure of SCOs.
     *
     * @return array of Sco
     *
     * @throws InvalidScormArchiveException
     */
    private function parseItemNodes(\DOMNode $source, \DOMNodeList $resources, Sco $parentSco = null)
    {
        $item = $source->firstChild;
        $scos = [];

        while (!is_null($item)) {
            if ('item' === $item->nodeName) {
                $sco = new Sco();
                $scos[] = $sco;
                $sco->setScoParent($parentSco);
                $this->findAttrParams($sco, $item, $resources);
                $this->findNodeParams($sco, $item->firstChild);

                if ($sco->isBlock()) {
                    $sco->setScoChildren($this->parseItemNodes($item, $resources, $sco));
                }
            }
            $item = $item->nextSibling;
        }

        return $scos;
    }

    private function parseResourceNodes(\DOMNodeList $resources)
    {
        $scos = [];

        foreach ($resources as $resource) {
            if (!is_null($resource->attributes)) {
                $scormType = $resource->attributes->getNamedItemNS(
                    $resource->lookupNamespaceUri('adlcp'),
                    'scormType'
                );

                if (!is_null($scormType) && 'sco' === $scormType->nodeValue) {
                    $identifier = $resource->attributes->getNamedItem('identifier');
                    $href = $resource->attributes->getNamedItem('href');

                    if (is_null($identifier)) {
                        throw new InvalidScormArchiveException('sco_with_no_identifier_message');
                    }
                    if (is_null($href)) {
                        throw new InvalidScormArchiveException('sco_resource_without_href_message');
                    }
                    $sco = new Sco();
                    $sco->setBlock(false);
                    $sco->setVisible(true);
                    $sco->setIdentifier($identifier->nodeValue);
                    $sco->setTitle($identifier->nodeValue);
                    $sco->setEntryUrl($href->nodeValue);
                    $scos[] = $sco;
                }
            }
        }

        return $scos;
    }

    /**
     * Initializes parameters of the SCO defined in attributes of the node.
     * It also look for the associated resource if it is a SCO and not a block.
     *
     * @throws InvalidScormArchiveException
     */
    private function findAttrParams(Sco $sco, \DOMNode $item, \DOMNodeList $resources)
    {
        $identifier = $item->attributes->getNamedItem('identifier');
        $isVisible = $item->attributes->getNamedItem('isvisible');
        $identifierRef = $item->attributes->getNamedItem('identifierref');
        $parameters = $item->attributes->getNamedItem('parameters');

        // throws an Exception if identifier is undefined
        if (is_null($identifier)) {
            throw new InvalidScormArchiveException('sco_with_no_identifier_message');
        }
        $sco->setIdentifier($identifier->nodeValue);

        // visible is true by default
        if (!is_null($isVisible) && 'false' === $isVisible) {
            $sco->setVisible(false);
        } else {
            $sco->setVisible(true);
        }

        // set parameters for SCO entry resource
        if (!is_null($parameters)) {
            $sco->setParameters($parameters->nodeValue);
        }

        // check if item is a block or a SCO. A block doesn't define identifierref
        if (is_null($identifierRef)) {
            $sco->setBlock(true);
        } else {
            $sco->setBlock(false);
            // retrieve entry URL
            $sco->setEntryUrl($this->findEntryUrl($identifierRef->nodeValue, $resources));
        }
    }

    /**
     * Initializes parameters of the SCO defined in children nodes.
     */
    private function findNodeParams(Sco $sco, \DOMNode $item)
    {
        while (!is_null($item)) {
            switch ($item->nodeName) {
                case 'title':
                    $sco->setTitle($item->nodeValue);
                    break;
                case 'adlcp:masteryscore':
                    $sco->setScoreToPassInt($item->nodeValue);
                    break;
                case 'adlcp:maxtimeallowed':
                case 'imsss:attemptAbsoluteDurationLimit':
                    $sco->setMaxTimeAllowed($item->nodeValue);
                    break;
                case 'adlcp:timelimitaction':
                case 'adlcp:timeLimitAction':
                    $action = strtolower($item->nodeValue);

                    if ('exit,message' === $action
                        || 'exit,no message' === $action
                        || 'continue,message' === $action
                        || 'continue,no message' === $action) {
                        $sco->setTimeLimitAction($action);
                    }
                    break;
                case 'adlcp:datafromlms':
                case 'adlcp:dataFromLMS':
                    $sco->setLaunchData($item->nodeValue);
                    break;
                case 'adlcp:prerequisites':
                    $sco->setPrerequisites($item->nodeValue);
                    break;
                case 'imsss:minNormalizedMeasure':
                    $sco->setScoreToPassDecimal($item->nodeValue);
                    break;
                case 'adlcp:completionThreshold':
                    if ($item->nodeValue && !is_nan($item->nodeValue)) {
                        $sco->setCompletionThreshold(floatval($item->nodeValue));
                    }
                    break;
            }
            $item = $item->nextSibling;
        }
    }

    /**
     * Searches for the resource with the given id and retrieve URL to its content.
     *
     * @return string URL to the resource associated to the SCO
     *
     * @throws InvalidScormArchiveException
     */
    public function findEntryUrl($identifierref, \DOMNodeList $resources)
    {
        foreach ($resources as $resource) {
            $identifier = $resource->attributes->getNamedItem('identifier');

            if (!is_null($identifier)) {
                $identifierValue = $identifier->nodeValue;

                if ($identifierValue === $identifierref) {
                    $href = $resource->attributes->getNamedItem('href');

                    if (is_null($href)) {
                        throw new InvalidScormArchiveException('sco_resource_without_href_message');
                    }

                    return $href->nodeValue;
                }
            }
        }
        throw new InvalidScormArchiveException('sco_without_resource_message');
    }
}
