<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 2/19/15
 */

namespace Icap\InwicastBundle\Manager;

use Doctrine\ORM\EntityManager;
use Icap\InwicastBundle\Entity\MediaCenter;
use Icap\InwicastBundle\Exception\InvalidMediacenterFormException;
use Icap\InwicastBundle\Exception\NoMediacenterException;
use Icap\InwicastBundle\Repository\MediacenterRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Service("inwicast.plugin.manager.mediacenter")
 */
class MediaCenterManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private $mediacenterRepository;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $formFactory;

    /**
     * @DI\InjectParams({
     *      "em"                    = @DI\Inject("doctrine.orm.entity_manager"),
     *      "formFactory"           = @DI\Inject("form.factory")
     * })
     */
    public function __construct(
        EntityManager $em,
        FormFactoryInterface $formFactory
    ) {
        $this->em = $em;
        $this->mediacenterRepository = $em->getRepository('IcapInwicastBundle:MediaCenter');
        $this->formFactory = $formFactory;
    }

    public function getMediacenter()
    {
        $mediacenter = $this->mediacenterRepository->findAll();
        if (sizeof($mediacenter) === 0) {
            throw new NoMediacenterException();
        } else {
            return $mediacenter[0];
        }
    }

    public function getMediacenterOrEmpty()
    {
        try {
            return $this->getMediacenter();
        } catch (NoMediacenterException $nme) {
            return $this->getEmptyMediacenter();
        }
    }

    public function getEmptyMediacenter()
    {
        return new MediaCenter();
    }

    public function getMediacenterForm(MediaCenter $mediacenter = null)
    {
        if ($mediacenter === null) {
            $mediacenter = $this->getMediacenterOrEmpty();
        }
        $form = $this->formFactory->create(
            'inwicast_plugin_type_mediacenter',
            $mediacenter
        );

        return $form;
    }

    public function processForm(MediaCenter $mediacenter, Request $request)
    {
        $form = $this->getMediacenterForm($mediacenter);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $mediacenter = $form->getData();
            $this->em->persist($mediacenter);
            $this->em->flush();

            return $mediacenter;
        }

        throw new InvalidMediacenterFormException('invalid_url', $form);
    }
}
