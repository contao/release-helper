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

use GitWrapper\GitWrapper;
use Psr\Log\LoggerInterface;

class StageChangesTask implements TaskInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $branchName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $rootDir
     * @param string               $branchName
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, string $branchName, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->branchName = $branchName;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $git = (new GitWrapper())->workingCopy($this->rootDir);

        if (!$git->hasChanges()) {
            return;
        }

        $git->add('-A');

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Staged the changes of the "%s" branch.', $this->branchName));
        }
    }
}
