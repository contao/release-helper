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

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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

        $finder = (new Finder())
            ->directories()
            ->in($this->buildDir.'/vendor/tecnickcom/tcpdf/fonts')
        ;

        $fs->remove($finder->getIterator());

        $finder = (new Finder())
            ->files()
            ->notName('courier.php')
            ->notName('freeserif*.*')
            ->notName('helvetica*.php')
            ->in($this->buildDir.'/vendor/tecnickcom/tcpdf/fonts')
        ;

        $fs->remove($finder->getIterator());

        $finder = (new Finder())
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
            ->notPath('twig/lib/Twig')
            ->in($this->buildDir.'/vendor')
        ;

        $fs->remove($finder->getIterator());

        if (null !== $this->logger) {
            $this->logger->notice('Purged the docs and tests folders.');
        }
    }
}
