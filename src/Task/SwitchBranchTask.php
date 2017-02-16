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
 * Switches the branch.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class SwitchBranchTask implements TaskInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $branch;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param string               $rootDir
     * @param string               $branch
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, string $branch, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->branch = $branch;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        (new GitWrapper())
            ->workingCopy($this->rootDir)
            ->checkout($this->branch)
        ;

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Switched to the "%s" branch.', $this->branch));
        }
    }
}
