<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Edition;

use Contao\ReleaseHelper\Task\CloneRepositoryTask;
use Contao\ReleaseHelper\Task\InstallDependenciesTask;
use Contao\ReleaseHelper\Task\InstallWebDirTask;
use Contao\ReleaseHelper\Task\PackArchivesTask;
use Contao\ReleaseHelper\Task\PurgeTestFilesTask;
use Contao\ReleaseHelper\Task\RemoveBuildDirTask;
use GitWrapper\GitWrapper;
use Psr\Log\LoggerInterface;

/**
 * Edition class.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Edition
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param string               $rootDir
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->logger = $logger;
    }

    /**
     * Builds the edition.
     */
    public function build(): void
    {
        $version = $this->getVersion();
        $buildDir = sprintf('%s/contao-%s', $this->rootDir, $version);

        (new CloneRepositoryTask($buildDir, $version, $this->logger))->run();
        (new InstallDependenciesTask($buildDir, $this->logger))->run();
        (new InstallWebDirTask($buildDir, $this->logger))->run();
        (new PurgeTestFilesTask($buildDir, $this->logger))->run();
        (new PackArchivesTask($this->rootDir, $version, $this->logger))->run();
        (new RemoveBuildDirTask($buildDir, $this->logger))->run();
    }

    /**
     * Returns the version.
     *
     * @return string
     */
    private function getVersion(): string
    {
        return trim((new GitWrapper())->git('describe --tags', $this->rootDir));
    }
}
