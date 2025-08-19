<?php

namespace DatPM\SlsTinker\Lambda;

class BrefInvocationResult extends InvocationResult
{
    public function getOutput()
    {
        return data_get($this->payload, 'output');
    }
}
