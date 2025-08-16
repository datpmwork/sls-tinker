<?php

namespace DatPM\SlsTinker\Shells;

class RemoteLambdaShell extends LambdaShell
{
    public function setScopeVariables(array $vars)
    {
        parent::setScopeVariables($vars);

        // Only write new context data when the context was restored
        if ($this->contextRestored) {
            $excludedSpecialVars = array_diff($this->getScopeVariables(false), $this->getSpecialScopeVariables(false));
            $this->writeContextData($excludedSpecialVars);
        }
    }
}
