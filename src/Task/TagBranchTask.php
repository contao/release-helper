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

class TagBranchTask implements TaskInterface
{
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
        (new GitWrapper())
            ->workingCopy($this->rootDir)
            ->tag($this->version)
            ->pushTag($this->version)
        ;

        if (null !== $this->logger) {
            $this->logger->notice('Tagged the current branch.');
        }
    }
}
