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

class InstallDependenciesTask implements TaskInterface
{
    /**
     * @var string
     */
    private $buildDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $buildDir
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $buildDir, LoggerInterface $logger = null)
    {
        $this->buildDir = $buildDir;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $finder = new ExecutableFinder();

        if (false === ($composer = $finder->find('composer', false))) {
            throw new \RuntimeException('The composer executable could not be found.');
        }

        $callback = function (string $type, string $buffer): void {
            $this->logger->info(trim($buffer));
        };

        $process = new Process('composer install --prefer-dist --no-dev --no-scripts', $this->buildDir);
        $process->run($callback);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (null !== $this->logger) {
            $this->logger->notice('Installed the dependencies.');
        }
    }
}
