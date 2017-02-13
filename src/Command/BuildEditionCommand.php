<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Command;

use Contao\ReleaseHelper\Edition\Edition;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Builds a Contao edition.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class BuildEditionCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('build')
            ->setDescription('Builds a Contao edition')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootDir = getcwd();

        if (!is_dir($rootDir.'/vendor/contao')) {
            throw new \RuntimeException('Please run the script from the application root directory.');
        }

        $status = 0;
        $logger = new ConsoleLogger($output, [LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL]);

        $question = new ChoiceQuestion(
            'Please select an edition:',
            [Edition::STANDARD_EDITION, Edition::MANAGED_EDITION]
        );

        $helper = $this->getHelper('question');
        $type = $helper->ask($input, $output, $question);

        try {
            (new Edition($type, $rootDir, $logger))->build();
        } catch (\RuntimeException $e) {
            $status = 1;
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        return $status;
    }
}
