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

class UpdateConstantsTask implements TaskInterface
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
        $constants = $this->rootDir.'/src/Resources/contao/config/constants.php';

        if (!file_exists($constants)) {
            return;
        }

        include $constants;

        if (!defined('VERSION') || !defined('BUILD')) {
            return;
        }

        $content = file_get_contents($constants);
        list($maj, $min, $build) = explode('.', $this->version);

        $content = str_replace(
            [
                sprintf("define('VERSION', '%s');", VERSION),
                sprintf("define('BUILD', '%s');", BUILD),
            ],
            [
                sprintf("define('VERSION', '%s.%s');", $maj, $min),
                sprintf("define('BUILD', '%s');", $build),
            ],
            $content
        );

        file_put_contents($constants, $content);

        if (null !== $this->logger) {
            $this->logger->notice('Updated the constants.php file.');
        }
    }
}
