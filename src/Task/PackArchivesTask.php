<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Task;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Packs the .zip and .tar.gz archive.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PackArchivesTask implements TaskInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $version;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param string               $rootDir
     * @param string               $version
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, string $version, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->version = $version;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->generateZip();
        $this->generateTar();

        if (null !== $this->logger) {
            $this->logger->notice('Packed the .zip and .tar.gz archive.');
        }
    }

    /**
     * Generates the .zip file.
     *
     * @throws \RuntimeException
     * @throws ProcessFailedException
     */
    private function generateZip(): void
    {
        $finder = new ExecutableFinder();

        if (false === ($zip = $finder->find('zip', false))) {
            throw new \RuntimeException('The zip executable could not be found.');
        }

        $process = new Process(
            sprintf(
                '%s -r contao-%s.zip contao-%s/',
                $zip,
                $this->version,
                $this->version
            ),
            $this->rootDir
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Generates the .tar.gz file.
     *
     * @throws \RuntimeException
     * @throws ProcessFailedException
     */
    private function generateTar(): void
    {
        $finder = new ExecutableFinder();

        if (false === ($tar = $finder->find('tar', false))) {
            throw new \RuntimeException('The tar executable could not be found.');
        }

        $process = new Process(
            sprintf(
                '%s -czf contao-%s.tar.gz contao-%s/',
                $tar,
                $this->version,
                $this->version
            ),
            $this->rootDir
        );

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
