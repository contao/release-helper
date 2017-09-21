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

use Psr\Log\LoggerInterface;

class UpdateChangelogTask implements TaskInterface
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
        $changeLog = $this->rootDir.'/CHANGELOG.md';

        if (!file_exists($changeLog)) {
            return;
        }

        $content = file_get_contents($changeLog);

        if (false === strpos($content, '### DEV')) {
            return;
        }

        $headline = sprintf('### %s (%s)', $this->version, date('Y-m-d'));

        file_put_contents($changeLog, str_replace('### DEV', $headline, $content));

        if (null !== $this->logger) {
            $this->logger->notice('Updated the CHANGELOG.md file.');
        }
    }
}
