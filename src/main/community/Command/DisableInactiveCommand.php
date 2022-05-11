<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Command;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Disables users which have not logged in the app since a date.
 */
class DisableInactiveCommand extends Command
{
    /** @var ObjectManager */
    private $om;
    /** @var UserManager */
    private $manager;

    const DATE_FORMAT = 'Y-m-d';

    public function __construct(
        ObjectManager $om,
        UserManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Disables users which have not logged in the app since a date.');
        $this->addArgument(
            'date',
            InputArgument::REQUIRED,
            sprintf('Users which have not logged in since this date will be disabled. Date format (%s).', static::DATE_FORMAT)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');
        if (false === \DateTime::createFromFormat(static::DATE_FORMAT, $date)) {
            // easy way of checking the date is in the correct format
            throw new \InvalidArgumentException(sprintf('Incorrect date. Expected a date string in the format : %s.', static::DATE_FORMAT));
        }

        $users = $this->om->getRepository(User::class)->findInactiveSince($date);

        $this->om->startFlushSuite();
        $output->writeln(sprintf('Found %d users inactive since %s.', count($users), $date));
        foreach ($users as $user) {
            $this->manager->disable($user);
        }
        $this->om->endFlushSuite();

        return 0;
    }
}
