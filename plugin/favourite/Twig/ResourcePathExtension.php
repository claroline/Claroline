<?php

namespace HeVinci\FavouriteBundle\Twig;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @Service
 * @Tag("twig.extension")
 */
class ResourcePathExtension extends \Twig_Extension
{
    protected $doctrine;
    protected $generator;

    /**
     * @DI\InjectParams({
     *     "doctrine"  = @DI\Inject("doctrine"),
     *     "generator" = @DI\Inject("router")
     * })
     */
    public function __construct(RegistryInterface $doctrine, UrlGeneratorInterface $generator)
    {
        $this->doctrine = $doctrine;
        $this->generator = $generator;
    }

    public function getFullResourcePath($fullPath)
    {
        $segments = explode('`', $fullPath);
        unset($segments[count($segments) - 1]);

        $fullResourcePath = array();

        foreach ($segments as $segment) {
            $segmentsOfNode = explode('-', $segment);
            $nodeId = $segmentsOfNode[count($segmentsOfNode) - 1];

            $node = $this->doctrine->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->find($nodeId);
            $resourceTypeName = $node->getResourceType()->getName();

            if ($resourceTypeName === 'directory') {
                $routing = $this->generator->generate('claro_desktop_open_tool', array(
                    'toolName' => 'resource_manager',
                ));
                $routing .= '#resources/'.$nodeId;
            } else {
                $routing = $this->generator->generate('claro_resource_open', array(
                    'resourceType' => $resourceTypeName,
                    'node' => $nodeId,
                ));
            }

            $fullResourcePath[] = array(
                'nodeName' => substr($segment, 0, -(strlen($nodeId) + 1)),
                'nodeOpenUrl' => $routing,
            );
        }

        return $fullResourcePath;
    }

    public function getFunctions()
    {
        return array(
            'getFullResourcePath' => new \Twig_Function_Method($this, 'getFullResourcePath'),
        );
    }

    public function getName()
    {
        return 'hevinci_favourite_widget';
    }
}
