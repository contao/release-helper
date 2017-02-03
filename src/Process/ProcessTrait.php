<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Process;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Process trait.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
trait ProcessTrait
{
    /**
     * Execute a command in a sub-process.
     *
     * @param string $command
     *
     * @return Process
     *
     * @throws ProcessFailedException
     */
    private function executeCommand(string $command): Process
    {
        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }
}
