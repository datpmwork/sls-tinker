<?php

namespace DatPM\SlsTinker\Shells;

use DatPM\SlsTinker\ShellListeners\LocalLoopListener;

class LocalLambdaShell extends LambdaShell
{
    protected function getDefaultLoopListeners(): array
    {
        $listeners = parent::getDefaultLoopListeners();

        $listeners[] = new LocalLoopListener($this->lambdaFunctionName);

        return $listeners;
    }
}
