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
    const STANDARD_EDITION = 'standard-edition';
    const MANAGED_EDITION = 'managed-edition';

    /**
     * @var string
     */
    private $type;

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
     * @param string               $type
     * @param string               $rootDir
     * @param LoggerInterface|null $logger
     *
     * @throws \RuntimeException
     */
    public function __construct(string $type, string $rootDir, LoggerInterface $logger = null)
    {
        $this->type = $type;
        $this->rootDir = $rootDir;
        $this->logger = $logger;

        if ($type !== self::STANDARD_EDITION && $type !== self::MANAGED_EDITION) {
            throw new \RuntimeException(sprintf('Invalid edition type "%s".', $type));
        }
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

        if ($this->type === self::MANAGED_EDITION) {
            (new InstallWebDirTask($buildDir, $this->logger))->run();
        }

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
