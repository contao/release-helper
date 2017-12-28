<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Bundle;

use Contao\ReleaseHelper\Task\CommitChangesTask;
use Contao\ReleaseHelper\Task\SwitchBranchTask;
use Contao\ReleaseHelper\Task\TagBranchTask;
use Contao\ReleaseHelper\Task\TransifexSyncTask;
use Contao\ReleaseHelper\Task\UpdateChangelogTask;
use Contao\ReleaseHelper\Task\UpdateConstantsTask;
use GitWrapper\GitWrapper;
use Psr\Log\LoggerInterface;

class Bundle
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $path;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $key
     * @param string               $rootDir
     * @param LoggerInterface|null $logger
     *
     * @throws \RuntimeException
     */
    public function __construct(string $key, string $rootDir, LoggerInterface $logger = null)
    {
        $this->key = $key;
        $this->logger = $logger;
        $this->path = $rootDir.'/vendor/'.$key;

        if (!is_dir($this->path)) {
            throw new \RuntimeException(sprintf('The bundle directory "vendor/%s" does not exist.', $key));
        }
    }

    /**
     * Releases the bundle.
     *
     * @param string $version
     *
     * @throws \RuntimeException
     */
    public function release(string $version): void
    {
        $branchName = $this->getBranchName();

        if (!preg_match('/^\d\.\d$/', $branchName)) {
            throw new \RuntimeException(sprintf('Cannot process branch "%s"', $branchName));
        }

        (new TransifexSyncTask($this->path, $this->logger))->run();
        (new UpdateChangelogTask($this->path, $version, $this->logger))->run();
        (new UpdateConstantsTask($this->path, $version, $this->logger))->run();
        (new CommitChangesTask($this->path, $branchName, 'Version '.$version.'.', $this->logger))->run();
        (new TagBranchTask($this->path, $version, $this->logger))->run();
    }

    /**
     * Switches the branch.
     *
     * @param string $target
     */
    public function switchBranch(string $target): void
    {
        (new SwitchBranchTask($this->path, $target, $this->logger))->run();
    }

    /**
     * Commits the current changes.
     *
     * @param string $message
     */
    public function commitChanges(string $message): void
    {
        (new CommitChangesTask($this->path, $this->getBranchName(), $message, $this->logger))->run();
    }

    /**
     * Returns the branch name.
     *
     * @return string
     */
    private function getBranchName(): string
    {
        $branchName = trim((new GitWrapper())->git('symbolic-ref --short HEAD', $this->path));

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Bundle "%s" is on branch "%s".', $this->key, $branchName));
        }

        return $branchName;
    }
}
