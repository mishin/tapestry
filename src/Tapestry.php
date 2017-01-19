<?php

namespace Tapestry;

use ArrayAccess;
use League\Event\Emitter;
use League\Container\Container;
use League\Container\ContainerInterface;
use League\Container\ReflectionContainer;
use League\Container\ContainerAwareInterface;
use League\Container\ServiceProvider\ServiceProviderInterface;
use Symfony\Component\Console\Input\InputInterface;

class Tapestry implements ContainerAwareInterface, ArrayAccess
{
    /**
     * The current globally available instance of Tapestry.
     *
     * @var static
     */
    protected static $instance;

    /**
     * @var \League\Container\ContainerInterface|\League\Container\Container
     */
    protected $container;

    /**
     * Version Number.
     *
     * @var string
     */
    const VERSION = '1.0.6-dev';

    /**
     * Tapestry constructor.
     *
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->parseOptions($input->getOptions());
        $this['events'] = new Emitter();
        $this->boot();
    }

    /**
     * @param array $options
     */
    private function parseOptions(array $options = [])
    {
        $this['cmd_options'] = $options;
        $this['environment'] = 'local';
        $this['currentWorkingDirectory'] = getcwd();

        if (isset($options['env'])) {
            $this['environment'] = $options['env'];
        }

        if (isset($options['site-dir'])) {
            $this['currentWorkingDirectory'] = $options['site-dir'];
        }

        // @todo have this implemented for #82
        $this['destinationDirectory'] = $this['currentWorkingDirectory'] . DIRECTORY_SEPARATOR . 'build_' . $this['environment'];

        if (isset($options['dist-dir'])) {
            $this['destinationDirectory'] = $options['dist-dir'];
        }
    }

    /**
     * @param InputInterface $arguments
     */
    public function setInput(InputInterface $arguments)
    {
        $this->parseOptions($arguments->getOptions());
    }

    /**
     * Register/Boot Providers.
     *
     * @return void
     */
    public function boot()
    {
        $this->register(\Tapestry\Providers\ProjectConfigurationServiceProvider::class);
        $this->register(\Tapestry\Providers\ProjectKernelServiceProvider::class);
        $this->register(\Tapestry\Providers\ProjectServiceProvider::class);
        $this->register(\Tapestry\Providers\CompileStepsServiceProvider::class);
        $this->register(\Tapestry\Providers\CommandServiceProvider::class);
        $this->register(\Tapestry\Providers\PlatesServiceProvider::class);
    }

    /**
     * Set a container.
     *
     * @param \League\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->container->delegate(
            new ReflectionContainer()
        );
        $this->container->add(self::class, $this);
        self::setInstance($this);
    }

    /**
     * Get the container.
     *
     * @return \League\Container\ContainerInterface
     */
    public function getContainer()
    {
        if (!isset($this->container)) {
            $this->setContainer(new Container());
        }

        return $this->container;
    }

    /**
     * Register a service provider.
     *
     * @param string|ServiceProviderInterface $serviceProvider
     *
     * @return void
     */
    public function register($serviceProvider)
    {
        $this->getContainer()->addServiceProvider($serviceProvider);
    }

    /**
     * @return Tapestry
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * @param Tapestry $tapestry
     */
    public static function setInstance(Tapestry $tapestry)
    {
        static::$instance = $tapestry;
    }

    /**
     * @return Emitter
     */
    public function getEventEmitter()
    {
        return $this['events'];
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getContainer()->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getContainer()->get($offset);
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->getContainer()->add($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     *
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        throw new \Exception("The container doesn't support removal of registered containers.");
    }
}
