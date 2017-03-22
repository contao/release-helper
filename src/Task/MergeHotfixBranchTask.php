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

/**
 * Merges the hotfix branch.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class MergeHotfixBranchTask implements TaskInterface
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
     * Constructor.
     *
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
        $workingCopy = (new GitWrapper())->workingCopy($this->rootDir);

        if ($workingCopy->hasChanges()) {
            $workingCopy
                ->add('-A')
                ->commit('Bump the version number.')
                ->push('origin', $this->branchName)
            ;
        }

        $workingCopy
            ->checkout('master')
            ->merge($this->branchName, ['m' => sprintf("Merge branch '%s'", $this->branchName)])
            ->push('origin', 'master')
        ;

        if (null !== $this->logger) {
            $this->logger->notice('Merged the hotfix branch.');
        }
    }
}
