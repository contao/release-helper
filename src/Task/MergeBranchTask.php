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

use GitWrapper\GitException;
use GitWrapper\GitWrapper;
use Psr\Log\LoggerInterface;

class MergeBranchTask implements TaskInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string               $rootDir
     * @param string               $from
     * @param string               $to
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, string $from, string $to, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->from = $from;
        $this->to = $to;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $git = (new GitWrapper())->workingCopy($this->rootDir);

        if (!$git->diff($this->from, $this->to)->getOutput()) {
            return;
        }

        $git
            ->checkout($this->to)
            ->reset(['hard' => true])
        ;

        try {
            $git->merge($this->from);
        } catch (GitException $e) {
            // don't stop on merge conflicts
        }

        if (null !== $this->logger) {
            $this->logger->notice(sprintf('Merged the "%s" branch into the "%s" branch.', $this->from, $this->to));
        }
    }
}
