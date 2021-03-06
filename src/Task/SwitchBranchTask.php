<?php

declare(strict_types=1);

/*
 * This file is part of the Contao release helper.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ReleaseHelper\Task;

use GitWrapper\GitWrapper;
use Psr\Log\LoggerInterface;

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
