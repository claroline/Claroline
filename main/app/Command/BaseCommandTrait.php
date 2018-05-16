<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait BaseCommandTrait
{
    use BaseCommandTrait;

    public function configureParams()
    {
        $args = [];

        foreach ($this->params as $param => $def) {
            $args[] = new InputArgument($param, InputArgument::REQUIRED, $def);
        }

        $this->setDefinition($args);
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->params as $parameter => $question) {
            if (!$input->getArgument($parameter)) {
                $input->setArgument(
                  $parameter,
                  $this->getHelper('question')->ask($input, $output, new Question($question.': '))
              );
            }
        }
    }
}
