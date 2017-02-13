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

use Contao\ReleaseHelper\Process\ProcessTrait;
use Psr\Log\LoggerInterface;

/**
 * Purges the test files.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PurgeTestFilesTask implements TaskInterface
{
    use ProcessTrait;

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
        $command = sprintf(
            '
                cd %s/contao-%s;
                rm -rf .git;
                [ -e var/cache/prod ] && rm -r var/cache/prod;
                find vendor/tecnickcom/tcpdf/fonts -type d -mindepth 1 -exec rm -r {} \;
                find vendor/tecnickcom/tcpdf/fonts -type f ! -name "courier.php" ! -name "freeserif*.*" ! -name "helvetica*.php" -exec rm {} \;
                find -E vendor -type d -iregex ".*/(docs?|examples|notes|sites|tests?)" -exec rm -r {} \;
            ',
            $this->rootDir,
            $this->version
        );

        $this->executeCommand($command);

        if (null !== $this->logger) {
            $this->logger->notice('Purged the test files.');
        }
    }
}
