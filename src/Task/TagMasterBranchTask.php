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
 * Tags the master branch.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TagMasterBranchTask implements TaskInterface
{
    use ProcessTrait;

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
        $command = sprintf(
            '
                cd %s;
                git checkout master;
                git tag %s;
                git push origin %s;
            ',
            $this->bundleDir,
            $this->version,
            $this->version
        );

        $this->executeCommand($command);

        if (null !== $this->logger) {
            $this->logger->notice('Tagged the master branch.');
        }
    }
}
