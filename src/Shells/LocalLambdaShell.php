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

    protected function writeStartupMessage()
    {
        parent::writeStartupMessage();

        $this->getRawOutput()->writeln("<info>You're running Tinker in AWS Lambda\nTarget Lambda: </info>"."<comment>{$this->lambdaFunctionName}</comment>");
    }
}
