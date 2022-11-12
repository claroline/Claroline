<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Command;

use Claroline\AppBundle\API\Crud;
use Claroline\ThemeBundle\Entity\Theme;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateThemeCommand extends Command
{
    /** @var Crud */
    private $crud;

    public function __construct(Crud $crud)
    {
        $this->crud = $crud;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new custom theme for the platform.')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the theme to create');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $output->writeln('Create a new theme "'.$name.'".');

        $this->crud->create(Theme::class, ['name' => $name], [Crud::NO_PERMISSIONS]);

        $output->writeln('Done !');

        return 0;
    }
}
