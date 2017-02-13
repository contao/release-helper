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
 * Merges the hotfix branch.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class MergeHotfixBranchTask implements TaskInterface
{
    use ProcessTrait;

    /**
     * @var string
     */
    private $bundleDir;

    /**
     * @var string
     */
    private $branchName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param string               $bundleDir
     * @param string               $branchName
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $bundleDir, string $branchName, LoggerInterface $logger = null)
    {
        $this->bundleDir = $bundleDir;
        $this->branchName = $branchName;
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
                git add -A;
                git commit -m "Bump the version number.";
                git checkout master;
                git merge -m "Merge branch \'%s\'" %s;
                git push origin master;
            ',
            $this->bundleDir,
            $this->branchName,
            $this->branchName
        );

        $this->executeCommand($command);

        if (null !== $this->logger) {
            $this->logger->notice('Merged the hotfix branch.');
        }
    }
}
