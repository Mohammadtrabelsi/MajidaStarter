<?php

namespace Tests\Feature;

use Tests\TestCase;

class MakeCrudModuleCommandTest extends TestCase
{
    public function test_rejects_names_that_are_not_studly_case(): void
    {
        $this->artisan('starter:make-crud', ['name' => 'bad name'])
            ->expectsOutputToContain('singular StudlyCase')
            ->assertExitCode(1);
    }

    public function test_refuses_to_overwrite_an_existing_model(): void
    {
        $this->artisan('starter:make-crud', ['name' => 'Post'])
            ->expectsOutputToContain('Refusing to overwrite')
            ->assertExitCode(1);
    }
}
