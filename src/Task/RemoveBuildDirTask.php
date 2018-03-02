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

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class RemoveBuildDirTask implements TaskInterface
{
    /**
     * @var string
     */
    private $buildDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $buildDir
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $buildDir, LoggerInterface $logger = null)
    {
        $this->buildDir = $buildDir;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        (new Filesystem())->remove($this->buildDir);

        if (null !== $this->logger) {
            $this->logger->notice('Removed the build directory.');
        }
    }
}
