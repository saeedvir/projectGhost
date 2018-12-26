<?php

namespace ProjectGhost;

use Illuminate\Support\ServiceProvider;

class ProjectGhostServiceProvider extends ServiceProvider
{
	protected $commands = [
        ProjectGhostCommand::class,
    ];

    public function boot()
    {
        # code...
    }

    public function register()
    {
		$this->commands($this->commands);

    }
}