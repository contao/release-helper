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

class CommitChangesTask implements TaskInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $message;

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
     * @param string               $message
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, string $branchName, string $message, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->branchName = $branchName;
        $this->message = $message;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $git = (new GitWrapper())->workingCopy($this->rootDir);

        if (!$git->diff('--staged')->getOutput()) {
            return;
        }

        $git
            ->commit($this->message)
            ->push('origin', $this->branchName)
        ;

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Commited the changes of the "%s" branch.', $this->branchName));
        }
    }
}
