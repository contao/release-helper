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
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Releases a new Contao version.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ReleaseCommand extends Command
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
            ->setName('release')
            ->setDefinition([
                new InputArgument('version', InputArgument::REQUIRED, 'The version number'),
            ])
            ->setDescription('Releases a new Contao version')
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

        foreach (self::bundles as $key) {
            try {
                (new Bundle($key, $rootDir, $logger))
                    ->release($input->getArgument('version'))
                ;
            } catch (\RuntimeException $e) {
                $status = 1;
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }

        return $status;
    }
}
