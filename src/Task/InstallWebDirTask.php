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
use Symfony\Component\Process\Process;

class InstallWebDirTask implements TaskInterface
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
        if (!is_dir($this->buildDir.'/app')) {
            mkdir($this->buildDir.'/app');
        }

        $process = new Process('vendor/bin/contao-console contao:install-web-dir', $this->buildDir);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (null !== $this->logger) {
            $this->logger->notice('Installed the web directory.');
        }
    }
}
