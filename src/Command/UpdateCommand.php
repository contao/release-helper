<?php

declare(strict_types=1);

/*
 * This file is part of the Contao release helper.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\ReleaseHelper\Command;

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasOption('rollback') && true === $input->getOption('rollback')) {
            return $this->rollback($output);
        }

        return $this->update($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('self-update')
            ->setDefinition([
                new InputOption('rollback', 'r', InputOption::VALUE_NONE, 'Roll back to the previous version'),
            ])
            ->setDescription('Updates the .phar file')
        ;
    }

    /**
     * Updates the .phar file.
     *
     * @param OutputInterface $output
     *
     * @return int
     */
    private function update(OutputInterface $output): int
    {
        $backupPath = getenv('HOME').'/.contao-release-helper';

        if (!is_dir($backupPath)) {
            mkdir($backupPath);
        }

        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->setBackupPath($backupPath.'/old.phar');

        /** @var GithubStrategy $strategy */
        $strategy = $updater->getStrategy();

        $strategy->setPackageName('contao/release-helper');
        $strategy->setPharName('contao-release-helper.phar');
        $strategy->setCurrentLocalVersion($this->getApplication()->getVersion());

        $result = $updater->update();

        if (false === $result) {
            $output->writeln('<info>Already up-to-date.</info>');
        } else {
            $output->writeln(
                sprintf(
                    'Updated from version %s to version %s.',
                    $updater->getOldVersion(),
                    $updater->getNewVersion()
                )
            );

            $output->writeln('Use <info>self-update --rollback</info> to return to the previous version.');
        }

        return 0;
    }

    /**
     * Rolls back the update.
     *
     * @param OutputInterface $output
     *
     * @return int
     */
    private function rollback(OutputInterface $output): int
    {
        $updater = new Updater(null, false);
        $updater->setRestorePath(getenv('HOME').'/.contao-release-helper/old.phar');

        $result = $updater->rollback();

        if (true === $result) {
            $output->writeln('<info>Successfully rolled back to the previous version.</info>');

            return 0;
        }
        $output->writeln('<error>Could not roll back to the previous version.</error>');

        return 1;
    }
}
