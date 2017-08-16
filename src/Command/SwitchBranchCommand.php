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

use Contao\ReleaseHelper\Bundle\Bundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Switches the bundle branches.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class SwitchBranchCommand extends Command
{
    const bundles = [
        'contao/core-bundle',
        'contao/calendar-bundle',
        'contao/comments-bundle',
        'contao/faq-bundle',
        'contao/installation-bundle',
        'contao/listing-bundle',
        'contao/manager-bundle',
        'contao/news-bundle',
        'contao/newsletter-bundle',
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('switch')
            ->setDefinition([
                new InputArgument('branch', InputArgument::REQUIRED, 'The target branch'),
            ])
            ->setDescription('Switches the bundle branches')
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
        $logger = new ConsoleLogger($output);

        foreach (self::bundles as $key) {
            try {
                (new Bundle($key, $rootDir, $logger))->switchBranch($input->getArgument('branch'));
            } catch (\RuntimeException $e) {
                $status = 1;
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }

        return $status;
    }
}
