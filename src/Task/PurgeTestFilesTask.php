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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Purges the test files.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class PurgeTestFilesTask implements TaskInterface
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
     * Constructor.
     *
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
        $fs = new Filesystem();
        $fs->remove($this->buildDir.'/.git');

        if (is_dir($this->buildDir.'/var/cache/prod')) {
            $fs->remove($this->buildDir.'/var/cache/prod');
        }

        /** @var SplFileInfo[] $files */
        $files = (new Finder())
            ->directories()
            ->in($this->buildDir.'/vendor/tecnickcom/tcpdf/fonts')
        ;

        foreach ($files as $file) {
            $fs->remove($file->getPathname());
        }

        /** @var SplFileInfo[] $files */
        $files = (new Finder())
            ->files()
            ->notName('courier.php')
            ->notName('freeserif*.*')
            ->notName('helvetica*.php')
            ->in($this->buildDir.'/vendor/tecnickcom/tcpdf/fonts')
        ;

        foreach ($files as $file) {
            $fs->remove($file->getPathname());
        }

        /** @var SplFileInfo[] $files */
        $files = (new Finder())
            ->directories()
            ->name('doc')
            ->name('docs')
            ->name('examples')
            ->name('notes')
            ->name('sites')
            ->name('test')
            ->name('tests')
            ->name('Test')
            ->name('Tests')
            ->in($this->buildDir.'/vendor')
        ;

        foreach ($files as $file) {
            if (is_dir($file->getPathname())) {
                $fs->remove($file->getPathname());
            }
        }

        if (null !== $this->logger) {
            $this->logger->notice('Purged the test files.');
        }
    }
}
