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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class InstallDependenciesTask implements TaskInterface
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
        $scripts = $this->removeScripts();
        $this->installDependencies();
        $this->restoreScripts($scripts);
    }

    /**
     * Removes the "scripts" section of the composer.json file.
     *
     * @return array
     */
    private function removeScripts(): array
    {
        $json = json_decode(file_get_contents($this->buildDir.'/composer.json'), true);

        $scripts = $json['scripts'];
        unset($json['scripts']);

        file_put_contents(
            $this->buildDir.'/composer.json',
            json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)
        );

        return $scripts;
    }

    /**
     * Re-adds the "script" section to the composer.json file.
     *
     * @param array $scripts
     */
    private function restoreScripts(array $scripts): void
    {
        $json = json_decode(file_get_contents($this->buildDir.'/composer.json'), true);
        $json['scripts'] = $scripts;

        file_put_contents(
            $this->buildDir.'/composer.json',
            json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Installs the dependencies.
     *
     * @throws \RuntimeException
     * @throws ProcessFailedException
     */
    private function installDependencies(): void
    {
        $finder = new ExecutableFinder();

        if (false === ($composer = $finder->find('composer', false))) {
            throw new \RuntimeException('The composer executable could not be found.');
        }

        $callback = function (string $type, string $buffer): void {
            $this->logger->info(trim($buffer));
        };

        $process = new Process($composer.' install --prefer-dist --no-dev', $this->buildDir);
        $process->run($callback);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (null !== $this->logger) {
            $this->logger->notice('Installed the dependencies.');
        }
    }
}
