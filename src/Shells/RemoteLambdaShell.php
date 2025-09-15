<?php

namespace DatPM\SlsTinker\Shells;

class RemoteLambdaShell extends LambdaShell
{
    public function setScopeVariables(array $vars)
    {
        parent::setScopeVariables($vars);

        // Only write new context data when the context was restored
        if ($this->contextRestored) {
            $specialVars = $this->getSpecialScopeVariables(false);
            $vars = $this->getScopeVariables(false);
            # Remove special vars from the list
            foreach (array_keys($specialVars) as $name) {
                unset($vars[$name]);
            }
            $this->writeContextData($vars);
        }
    }
}
