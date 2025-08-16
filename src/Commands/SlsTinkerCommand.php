<?php

namespace DatPM\SlsTinker\Commands;

use Psy\Configuration;
use Illuminate\Support\Env;
use Psy\VersionUpdater\Checker;
use DatPM\SlsTinker\Shells\LambdaShell;
use Laravel\Tinker\ClassAliasAutoloader;
use Laravel\Tinker\Console\TinkerCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SlsTinkerCommand extends TinkerCommand
{
    protected $name = 'sls-tinker';

    public function handle()
    {
        $this->getApplication()->setCatchExceptions(false);

        $config = Configuration::fromInput($this->input);
        $config->setUpdateCheck(Checker::NEVER);

        $config->getPresenter()->addCasters(
            $this->getCasters()
        );

        if ($this->option('execute')) {
            $config->setRawOutput(true);
        }

        $lambdaFunctionName = $this->argument('lambda');
        $shell = LambdaShell::newLambdaShell($config, $lambdaFunctionName);
        $shell->addCommands($this->getCommands());
        $shell->setIncludes($this->argument('include'));

        $path = Env::get('COMPOSER_VENDOR_DIR', $this->getLaravel()->basePath().DIRECTORY_SEPARATOR.'vendor');

        $path .= '/composer/autoload_classmap.php';

        $config = $this->getLaravel()->make('config');

        $loader = ClassAliasAutoloader::register(
            $shell, $path, $config->get('tinker.alias', []), $config->get('tinker.dont_alias', [])
        );

        if ($code = $this->option('execute')) {
            if ($context = $this->option('context')) {
                $shell->restoreContextData($context);
            }

            try {
                $shell->setOutput($this->output);
                $shell->execute($code);
            } finally {
                $loader->unregister();
            }

            return 0;
        }

        try {
            return $shell->run();
        } finally {
            $loader->unregister();
        }
    }

    protected function getArguments()
    {
        return [
            ['lambda', InputArgument::REQUIRED, 'Lambda Function Name'],
            ['include', InputArgument::IS_ARRAY, 'Include file(s) before starting tinker'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['execute', null, InputOption::VALUE_OPTIONAL, 'Execute the given code using Tinker'],
            ['context', null, InputOption::VALUE_OPTIONAL, 'The context data contains the defined vars'],
        ];
    }
}
