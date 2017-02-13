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
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Synchronizes the translations.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class TransifexSyncTask implements TaskInterface
{
    use ProcessTrait;

    /**
     * @var string
     */
    private $bundleDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param string               $bundleDir
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $bundleDir, LoggerInterface $logger = null)
    {
        $this->bundleDir = $bundleDir;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
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
     * Returns the HTTP client.
     *
     * @return Client
     */
    private function getClient(): Client
    {
        if (null === $this->client) {
            $this->client = new Client([
                'base_uri' => sprintf(
                    'https://www.transifex.com/api/2/project/%s/',
                    'contao-'.basename($this->bundleDir)
                ),
                'auth' => $this->getTransifexCredentials(),
            ]);
        }

        return $this->client;
    }

    /**
     * Returns the languages.
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    private function getLanguages(): array
    {
        $response = $this->getClient()->request('GET', 'languages');

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException($response->getReasonPhrase());
        }

        return json_decode($response->getBody());
    }

    /**
     * Returns the language details.
     *
     * @param string $language
     *
     * @return \stdClass
     *
     * @throws \RuntimeException
     */
    private function getLanguageDetails(string $language): \stdClass
    {
        $response = $this->getClient()->request('GET', sprintf('language/%s?details', $language));

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException($response->getReasonPhrase());
        }

        return json_decode($response->getBody());
    }

    /**
     * Returns the Transifex credentials.
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    private function getTransifexCredentials(): array
    {
        $runcomFile = getenv('HOME').'/.transifexrc';

        if (!file_exists($runcomFile)) {
            throw new \RuntimeException(sprintf('The Transifex runcom file "%s" does not exist.', $runcomFile));
        }

        $config = parse_ini_file($runcomFile);

        return [$config['username'], $config['password']];
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
        $txFile = $this->bundleDir.'/.tx/config';

        if (!file_exists($txFile)) {
            throw new \RuntimeException(sprintf('The Transifex configuration file "%s" does not exist.', $txFile));
        }

        $tx = parse_ini_file($txFile, true);

        foreach ($tx as $key => $settings) {
            if ('main' === $key) {
                continue;
            }

            $target = $this->bundleDir.'/'.str_replace('<lang>', $language, $settings['file_filter']);
            $targetDir = $this->bundleDir.'/'.dirname($target);

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
        list(, $resource) = explode('.', $key);

        $response = $this->getClient()->request('GET', sprintf('resource/%s/translation/%s', $resource, $language));

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException($response->getReasonPhrase());
        }

        return json_decode($response->getBody())->content;
    }

    /**
     * Removes a translation.
     *
     * @param string $language
     */
    private function removeTranslation(string $language): void
    {
        $folder = $this->bundleDir.'/src/Resources/contao/languages/'.$language;

        if (!is_dir($folder)) {
            return;
        }

        (new Filesystem())->remove($folder);
    }
}
