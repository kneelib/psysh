<?php

/*
 * This file is part of PsySH
 *
 * (c) 2012 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Command;

use Psy\Command\Command;
use Psy\Output\ShellOutput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Help command.
 *
 * Lists available commands, and gives command-specific help when asked nicely.
 */
class HelpCommand extends Command
{
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('help')
            ->setAliases(array('?'))
            ->setDefinition(array(
                new InputArgument('command_name', InputArgument::OPTIONAL, 'The command name', null),
            ))
            ->setDescription('Show a list of commands. Type `help [foo]` for information about [foo].')
            ->setHelp("My. How meta.")
        ;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->command !== null) {
            // help for an individual command
            $output->page($this->command->asText());
            $this->command = null;
        } elseif ($name = $input->getArgument('command_name')) {
            // help for an individual command
            $output->page($this->getApplication()->get($name)->asText());
        } else {
            // list available commands
            $commands = $this->getApplication()->all();

            $width = 0;
            foreach ($commands as $command) {
                $width = strlen($command->getName()) > $width ? strlen($command->getName()) : $width;
            }
            $width += 2;

            foreach ($commands as $name => $command) {
                if ($name !== $command->getName()) {
                    continue;
                }

                if ($command->getAliases()) {
                    $aliases = sprintf('  <comment>Aliases:</comment> %s', implode(', ', $command->getAliases()));
                } else {
                    $aliases = '';
                }

                $messages[] = sprintf("  <info>%-${width}s</info> %s%s", $name, $command->getDescription(), $aliases);
            }

            $output->page($messages);
        }
    }
}
