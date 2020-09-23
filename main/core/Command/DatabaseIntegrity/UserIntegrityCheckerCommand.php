<?php

namespace Claroline\CoreBundle\Command\DatabaseIntegrity;

use Claroline\CoreBundle\Command\AdminCliCommand;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserIntegrityCheckerCommand extends Command implements AdminCliCommand
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->userManager->bindUserToOrganization();

        return 0;
    }
}
