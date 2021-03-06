<?php

declare(strict_types=1);

/*
 * This file is part of the Contao release helper.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ReleaseHelper\Command;

use Contao\ReleaseHelper\Bundle\Bundle;
use Contao\ReleaseHelper\Edition\Edition;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class CommitCommand extends Command
{
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
        $logger = new ConsoleLogger($output);

        foreach (Edition::BUNDLES as $key) {
            try {
                (new Bundle($key, $rootDir, $logger))->commitChanges($input->getArgument('message'));
            } catch (\RuntimeException $e) {
                $status = 1;
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }

        return $status;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('commit')
            ->setDefinition([
                new InputArgument('message', InputArgument::REQUIRED, 'The commit message'),
            ])
            ->setDescription('Commits the current changes')
        ;
    }
}
