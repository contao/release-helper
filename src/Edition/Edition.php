<?php

declare(strict_types=1);

/*
 * This file is part of the Contao release helper.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
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

class Edition
{
    public const BUNDLES = [
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
     * @var string
     */
    private $rootDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
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
