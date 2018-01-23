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

use BabDev\Transifex\Languages;
use BabDev\Transifex\Transifex;
use BabDev\Transifex\Translations;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class TransifexSyncTask implements TaskInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var Transifex
     */
    private $transifex;

    /**
     * @param string               $rootDir
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $rootDir, LoggerInterface $logger = null)
    {
        $this->rootDir = $rootDir;
        $this->logger = $logger;
        $this->slug = 'contao-'.basename($this->rootDir);

        $this->initializeTransifex();
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        if (!file_exists($this->rootDir.'/.tx/config')) {
            return;
        }

        $languages = $this->getLanguages();

        foreach ($languages as $language) {
            try {
                $details = $this->getLanguageDetails($language->language_code);
            } catch (ClientException $e) {
                if (null !== $this->logger) {
                    $this->logger->info(sprintf('Skipped "%s" (0%% complete)', $language->language_code));
                }

                continue;
            }

            $rate = round($details->translated_segments / $details->total_segments * 100);

            if ($rate >= 95) {
                $this->addTranslation($language->language_code);

                if (null !== $this->logger) {
                    $this->logger->info(sprintf('Added "%s" (%d%% complete)', $language->language_code, $rate));
                }
            } else {
                $this->removeTranslation($language->language_code);

                if (null !== $this->logger) {
                    $this->logger->info(sprintf('Skipped "%s" (%d%% complete)', $language->language_code, $rate));
                }
            }
        }

        if (null !== $this->logger) {
            $this->logger->notice('Transifex synchronization complete.');
        }
    }

    /**
     * Initializes the Transifex client.
     *
     * @throws \RuntimeException
     */
    private function initializeTransifex(): void
    {
        $runcomFile = getenv('HOME').'/.transifexrc';

        if (!file_exists($runcomFile)) {
            throw new \RuntimeException(sprintf('The Transifex runcom file "%s" does not exist.', $runcomFile));
        }

        $config = parse_ini_file($runcomFile);

        $options = [
            'api.username' => $config['username'],
            'api.password' => $config['password'],
        ];

        $this->transifex = new Transifex($options);
    }

    /**
     * Returns the languages.
     *
     * @return array
     */
    private function getLanguages(): array
    {
        /** @var Languages $languages */
        $languages = $this->transifex->get('languages');

        $response = $languages->getLanguages($this->slug);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Returns the language details.
     *
     * @param string $language
     *
     * @return \stdClass
     */
    private function getLanguageDetails(string $language): \stdClass
    {
        /** @var Languages $languages */
        $languages = $this->transifex->get('languages');

        $response = $languages->getLanguage($this->slug, $language, true);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Adds a translation.
     *
     * @param string $language
     *
     * @throws \RuntimeException
     */
    private function addTranslation(string $language): void
    {
        $txFile = $this->rootDir.'/.tx/config';

        if (!file_exists($txFile)) {
            throw new \RuntimeException(sprintf('The Transifex configuration file "%s" does not exist.', $txFile));
        }

        $tx = parse_ini_file($txFile, true);

        foreach ($tx as $key => $settings) {
            if ('main' === $key) {
                continue;
            }

            $target = $this->rootDir.'/'.str_replace('<lang>', $language, $settings['file_filter']);
            $targetDir = \dirname($target);

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            file_put_contents($target, $this->getXliffContent($key, $language));
        }
    }

    /**
     * Returns the XLIFF content.
     *
     * @param string $key
     * @param string $language
     *
     * @return string
     */
    private function getXliffContent(string $key, string $language): string
    {
        [, $resource] = explode('.', $key);

        /** @var Translations $translations */
        $translations = $this->transifex->get('translations');

        $response = $translations->getTranslation($this->slug, $resource, $language);

        return json_decode($response->getBody()->getContents())->content;
    }

    /**
     * Removes a translation.
     *
     * @param string $language
     */
    private function removeTranslation(string $language): void
    {
        $folder = $this->rootDir.'/src/Resources/contao/languages/'.$language;

        if (!is_dir($folder)) {
            return;
        }

        (new Filesystem())->remove($folder);
    }
}
