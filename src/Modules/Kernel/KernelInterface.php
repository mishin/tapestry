<?php

namespace Tapestry\Modules\Kernel;

interface KernelInterface
{
    /**
     * This method is executed by Tapestry when the Kernel is registered.
     *
     * @return void
     */
    public function register();

    /**
     * This method of executed by Tapestry as part of the build process.
     *
     * @return void
     */
    public function boot();
}
