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

class CloneRepositoryTask implements TaskInterface
{
    /**
     * @var string
     */
    private $buildDir;

    /**
     * @var string
     */
    private $version;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $buildDir
     * @param string               $version
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $buildDir, string $version, LoggerInterface $logger = null)
    {
        $this->buildDir = $buildDir;
        $this->version = $version;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        (new GitWrapper())
            ->cloneRepository(\dirname($this->buildDir), $this->buildDir)
            ->checkout($this->version)
            ->reset(['hard' => true])
        ;

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Cloned the repository into the "%s" folder.', basename($this->buildDir)));
        }
    }
}
