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

use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Listener\Exception\InvalidScormArchiveException;
use JMS\DiExtraBundle\Annotation as DI;

//how is this similar to the sco2004 ?

/**
 * @DI\Service("claroline.library.scorm_12")
 */
class Scorm12
{
    /**
     * Looks for the organization to use.
     *
     * @param \DOMDocument $dom
     *
     * @return array of Scorm12Sco
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
                    && $organization->nodeName !== 'organization') {
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
                    && ($organization->nodeName !== 'organization'
                        || is_null($organization->attributes->getNamedItem('identifier'))
                        || $organization->attributes->getNamedItem('identifier')->nodeValue !== $defaultOrganization)) {
                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {
                    throw new InvalidScormArchiveException('default_organization_not_found_message');
                }
            }

            return $this->parseItemNodes($organization, $resources);
        }
    }

    /**
     * Creates defined structure of SCOs.
     *
     * @param \DOMNode     $source
     * @param \DOMNodeList $resources
     *
     * @return array of Scorm12Sco
     *
     * @throws InvalidScormArchiveException
     */
    private function parseItemNodes(
        \DOMNode $source,
        \DOMNodeList $resources,
        Scorm12Sco $parentSco = null
    ) {
        $item = $source->firstChild;
        $scos = array();

        while (!is_null($item)) {
            if ($item->nodeName === 'item') {
                $sco = new Scorm12Sco();
                $scos[] = $sco;
                $sco->setScoParent($parentSco);
                $this->findAttrParams($sco, $item, $resources);
                $this->findNodeParams($sco, $item->firstChild);

                if ($sco->getIsBlock()) {
                    $scos[] = $this->parseItemNodes($item, $resources, $sco);
                }
            }
            $item = $item->nextSibling;
        }

        return $scos;
    }

    /**
     * Initializes parameters of the SCO defined in attributes of the node.
     * It also look for the associated resource if it is a SCO and not a block.
     *
     * @param Scorm12Sco   $sco
     * @param \DOMNode     $item
     * @param \DOMNodeList $resources
     *
     * @throws InvalidScormArchiveException
     */
    private function findAttrParams(
        Scorm12Sco $sco,
        \DOMNode $item,
        \DOMNodeList $resources
    ) {
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
        if (!is_null($isVisible) && $isVisible === 'false') {
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
            $sco->setIsBlock(true);
        } else {
            $sco->setIsBlock(false);
            // retrieve entry URL
            $sco->setEntryUrl(
                $this->findEntryUrl($identifierRef->nodeValue, $resources)
            );
        }
    }

    /**
     * Searches for the resource with the given id and retrieve URL to its content.
     *
     * @param string       $identifierref id of the resource associated to the SCO
     * @param \DOMNodeList $resources
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

    /**
     * Initializes parameters of the SCO defined in children nodes.
     *
     * @param Scorm12Sco $sco
     * @param \DOMNode   $item
     */
    private function findNodeParams(Scorm12Sco $sco, \DOMNode $item)
    {
        while (!is_null($item)) {
            switch ($item->nodeName) {
                case 'title':
                    $sco->setTitle($item->nodeValue);
                    break;
                case 'adlcp:masteryscore':
                    $sco->setMasteryScore($item->nodeValue);
                    break;
                case 'adlcp:maxtimeallowed':
                    $sco->setMaxTimeAllowed($item->nodeValue);
                    break;
                case 'adlcp:timelimitaction':
                    $action = strtolower($item->nodeValue);

                    if ($action === 'exit,message'
                        || $action === 'exit,no message'
                        || $action === 'continue,message'
                        || $action === 'continue,no message') {
                        $sco->setTimeLimitAction($action);
                    }
                    break;
                case 'adlcp:datafromlms':
                    $sco->setLaunchData($item->nodeValue);
                    break;
                case 'adlcp:prerequisites':
                    $sco->setPrerequisites($item->nodeValue);
                    break;
            }
            $item = $item->nextSibling;
        }
    }
}
