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

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * The PsySH base command.
 */
abstract class Command extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function asText()
    {
        $messages = array(
            '<comment>Usage:</comment>',
            ' '.$this->getSynopsis(),
            '',
        );

        if ($this->getAliases()) {
            $messages[] = $this->aliasesAsText();
        }

        if ($this->getArguments()) {
            $messages[] = $this->argumentsAsText();
        }

        if ($this->getOptions()) {
            $messages[] = $this->optionsAsText();
        }

        if ($help = $this->getProcessedHelp()) {
            $messages[] = '<comment>Help:</comment>';
            $messages[] = ' '.str_replace("\n", "\n ", $help)."\n";
        }

        return implode("\n", $messages);
    }

    /**
     * {@inheritdoc}
     */
    private function getArguments()
    {
        return $this->getNativeDefinition()->getArguments();
    }

    /**
     * {@inheritdoc}
     */
    private function getOptions()
    {
        $hidden = $this->getHiddenOptions();
        return array_filter($this->getNativeDefinition()->getOptions(), function($option) use ($hidden) {
            return !in_array($option->getName(), $hidden);
        });
    }

    protected function getHiddenOptions()
    {
        return array('help', 'verbose');
    }

    private function aliasesAsText()
    {
        return '<comment>Aliases:</comment> <info>'.implode(', ', $this->getAliases()).'</info>'.PHP_EOL;
    }

    private function argumentsAsText()
    {
        $max = $this->getMaxWidth();
        $messages = array();

        $arguments = $this->getArguments();
        if ($arguments) {
            $messages[] = '<comment>Arguments:</comment>';
            foreach ($arguments as $argument) {
                if (null !== $argument->getDefault() && (!is_array($argument->getDefault()) || count($argument->getDefault()))) {
                    $default = sprintf('<comment> (default: %s)</comment>', $this->formatDefaultValue($argument->getDefault()));
                } else {
                    $default = '';
                }

                $description = str_replace("\n", "\n".str_pad('', $max + 2, ' '), $argument->getDescription());

                $messages[] = sprintf(" <info>%-${max}s</info> %s%s", $argument->getName(), $description, $default);
            }

            $messages[] = '';
        }

        return implode(PHP_EOL, $messages);
    }

    private function optionsAsText()
    {
        $max = $this->getMaxWidth();
        $messages = array();

        $options = $this->getOptions();
        if ($options) {
            $messages[] = '<comment>Options:</comment>';

            foreach ($options as $option) {
                if ($option->acceptValue() && null !== $option->getDefault() && (!is_array($option->getDefault()) || count($option->getDefault()))) {
                    $default = sprintf('<comment> (default: %s)</comment>', $this->formatDefaultValue($option->getDefault()));
                } else {
                    $default = '';
                }

                $multiple = $option->isArray() ? '<comment> (multiple values allowed)</comment>' : '';
                $description = str_replace("\n", "\n".str_pad('', $max + 2, ' '), $option->getDescription());

                $optionMax = $max - strlen($option->getName()) - 2;
                $messages[] = sprintf(" <info>%s</info> %-${optionMax}s%s%s%s",
                    '--'.$option->getName(),
                    $option->getShortcut() ? sprintf('(-%s) ', $option->getShortcut()) : '',
                    $description,
                    $default,
                    $multiple
                );
            }

            $messages[] = '';
        }

        return implode(PHP_EOL, $messages);
    }

    private function getMaxWidth()
    {
        $max = 0;

        foreach ($this->getOptions() as $option) {
            $nameLength = strlen($option->getName()) + 2;
            if ($option->getShortcut()) {
                $nameLength += strlen($option->getShortcut()) + 3;
            }

            $max = max($max, $nameLength);
        }

        foreach ($this->getArguments() as $argument) {
            $max = max($max, strlen($argument->getName()));
        }

        return ++$max;
    }

    private function formatDefaultValue($default)
    {
        if (is_array($default) && $default === array_values($default)) {
            return sprintf("array('%s')", implode("', '", $default));
        }

        return str_replace("\n", '', var_export($default, true));
    }
}
