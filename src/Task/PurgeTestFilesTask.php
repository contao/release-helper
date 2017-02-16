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
        $fs = new Filesystem();
        $buildDir = sprintf('%s/contao-%s', $this->rootDir, $this->version);

        $fs->remove($buildDir.'/.git');

        if (is_dir($buildDir.'/var/cache/prod')) {
            $fs->remove($buildDir.'/var/cache/prod');
        }

        /** @var SplFileInfo[] $files */
        $files = (new Finder())
            ->directories()
            ->in($buildDir.'/vendor/tecnickcom/tcpdf/fonts')
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
            ->in($buildDir.'/vendor/tecnickcom/tcpdf/fonts')
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
            ->in($buildDir.'/vendor')
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
