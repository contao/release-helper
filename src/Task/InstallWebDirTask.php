<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Task;

use Contao\ReleaseHelper\Process\ProcessTrait;
use Psr\Log\LoggerInterface;

/**
 * Installs the web directory.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class InstallWebDirTask implements TaskInterface
{
    use ProcessTrait;

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
        $command = sprintf(
            '
                cd %s/contao-%s;
                mkdir app;
                vendor/bin/contao-console contao:install-web-dir;
            ',
            $this->rootDir,
            $this->version
        );

        $this->executeCommand($command);

        if (null !== $this->logger) {
            $this->logger->notice('Installed the web directory.');
        }
    }
}
