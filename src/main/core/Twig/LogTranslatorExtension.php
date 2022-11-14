<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 11/18/15
 */

namespace Claroline\CoreBundle\Twig;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Library\RoutingHelper;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LogTranslatorExtension extends AbstractExtension
{
    private $helper;
    private $translator;
    private $templating;

    public function __construct(RoutingHelper $helper, TranslatorInterface $translator, Environment $templating)
    {
        $this->helper = $helper;
        $this->translator = $translator;
        $this->templating = $templating;
    }

    public function getName()
    {
        return 'twig_log_translator';
    }

    public function getFunctions()
    {
        return [
            'translateLog' => new TwigFunction('translateLog', [$this, 'translateLog']),
        ];
    }

    public function translateLog(Log $log)
    {
        $resource = $this->templating->render('@ClarolineCore/log/view_list_item_resource.html.twig', ['log' => $log]);
        $receiverUser = $this->templating->render('@ClarolineCore/log/view_list_item_receiver_user.html.twig', ['log' => $log]);
        $receiverGroup = $this->templating->render('@ClarolineCore/log/view_list_item_receiver_group.html.twig', ['log' => $log]);
        $role = $this->templating->render('@ClarolineCore/log/view_list_item_role.html.twig', ['log' => $log]);
        $workspace = $this->templating->render('@ClarolineCore/log/view_list_item_workspace.html.twig', ['log' => $log]);
        $tool = $this->templating->render('@ClarolineCore/log/view_list_item_tool.html.twig', ['log' => $log]);

        $data = [
          '%resource%' => $resource,
          '%receiver_user%' => $receiverUser,
          '%receiver_group%' => $receiverGroup,
          '%role%' => $role,
          '%workspace%' => $workspace,
          '%tool%' => $tool,
        ];

        $details = $log->getDetails();

        $utils = new ArrayUtils();

        $properties = $utils->getPropertiesName($details);

        foreach ($properties as $prop) {
            $data['%'.$prop.'%'] = $utils->get($details, $prop);
        }

        return $this->translator->trans('log_'.$log->getAction().'_sentence', $data, 'log');
    }
}
