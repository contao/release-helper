<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Task;

use Psr\Log\LoggerInterface;

/**
 * Updates the constants.php file.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class UpdateConstantsTask implements TaskInterface
{
    /**
     * @var string
     */
    private $bundleDir;

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
     * @param string               $bundleDir
     * @param string               $version
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $bundleDir, string $version, LoggerInterface $logger = null)
    {
        $this->bundleDir = $bundleDir;
        $this->version = $version;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $constants = $this->bundleDir.'/src/Resources/contao/config/constants.php';

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
