<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\Handler\FormHandler;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Manager\ResultManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Response;

/**
 * @EXT\Route(requirements={"id"="\d+", "abilityId"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ResultController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.result.result_manager"),
     *     "handler" = @DI\Inject("claroline.form_handler")
     * })
     *
     * @param ResultManager     $manager
     * @param FormHandler       $handler
     */
    public function __construct(ResultManager $manager, FormHandler $handler)
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
    }

    /**
     * @EXT\Route("/{id}", name="claroline_open_result")
     * @EXT\Template
     *
     * @param Result $result
     * @return array
     */
    public function resultAction(Result $result)
    {
        // check access

        return ['result' => $result];
    }
}
