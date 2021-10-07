<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DevBundle\Command;

use Claroline\AppBundle\Routing\Finder;
use Symfony\Bundle\FrameworkBundle\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RouterDebugCommand extends Command
{
    /** @var Finder */
    private $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Shows the api route')
            ->setDefinition(
                [new InputArgument('class', InputArgument::REQUIRED, 'The class managed by the api.')]
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getArgument('class');
        $describeCollection = $this->finder->find($class);
        $io = new SymfonyStyle($input, $output);
        $helper = new DescriptorHelper();
        $helper->describe($io, $describeCollection, []);

        return 0;
    }
}
