<?php

namespace Claroline\CoreBundle\Library\Player;

interface PlayerInterface
{
    public function indexAction($workspaceId);
    public function getMimeType();
    public function getPlayerName();
}