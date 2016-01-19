<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Command\Dev;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CssCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('claroline:css');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $twig = $this->getContainer()->get('templating');

        $variables = [
            'bodyBg' => '#756',
            'brandPrimary' => '#657',
            'brandSuccess' => '#856',
            'brandInfo' => '#124',
            'brandWarning' => '#965',
            'brandDanger' => '#257',
            'fontFamilyBase' => 'Ubuntu',
            'navbarInverseBg' => '#333',
            'navbarBottomBorder' => '#423',
        ];

        $css = $twig->render('ClarolineCoreBundle:Theme:additionalStyles.css.twig', $variables);

        $output->writeln($css);
    }
}
