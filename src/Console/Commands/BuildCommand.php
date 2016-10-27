<?php namespace Tapestry\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tapestry\Entities\Project;

class BuildCommand extends Command
{
    /**
     * Current Working Directory as set by user input --site-dir or getcwd() by default.
     * @var string
     */
    private $currentWorkingDirectory;
    /**
     * @var array
     */
    private $steps;

    /**
     * @var string
     */
    private $environment;

    /**
     * InitCommand constructor.
     * @param array $steps
     * @param string $currentWorkingDirectory
     * @param $environment
     */
    public function __construct(array $steps, $currentWorkingDirectory, $environment)
    {
        parent::__construct();
        $this->currentWorkingDirectory = $currentWorkingDirectory;
        $this->steps = $steps;
        $this->environment = $environment;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build Project.');
    }

    protected function fire()
    {
        $project = new Project($this->steps, $this->currentWorkingDirectory, $this->environment);
        $project->setOutput($this->output);
        $project->compile();
    }
}