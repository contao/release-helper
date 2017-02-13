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

use Contao\ReleaseHelper\Process\ProcessTrait;
use Psr\Log\LoggerInterface;

/**
 * Packs the .zip and .tar.gz archive.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PackArchivesTask implements TaskInterface
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
                cd %s;
                zip -r contao-%s.zip contao-%s/;
                tar -czf contao-%s.tar.gz contao-%s/;
            ',
            $this->rootDir,
            $this->version,
            $this->version,
            $this->version,
            $this->version
        );

        $this->executeCommand($command);

        if (null !== $this->logger) {
            $this->logger->notice('Packed the .zip and .tar.gz archive.');
        }
    }
}
