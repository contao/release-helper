<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ReleaseHelper\Task;

/**
 * Task interface.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
interface TaskInterface
{
    /**
     * Runs the task.
     */
    public function run(): void;
}
