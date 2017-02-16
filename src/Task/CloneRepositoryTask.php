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
 * Clones the repository.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class CloneRepositoryTask implements TaskInterface
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
     * Constructor.
     *
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
            ->cloneRepository($this->rootDir, sprintf('%s/contao-%s', $this->rootDir, $this->version))
            ->checkout($this->version)
            ->reset(['hard' => true])
        ;

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Cloned the repository into the "contao-%s" folder.', $this->version));
        }
    }
}
