<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_locale_can_be_switched(): void
    {
        $this->from('/login')
            ->get('/language/ar')
            ->assertRedirect('/login')
            ->assertSessionHas('locale', 'ar');
    }

    public function test_unsupported_locale_is_ignored(): void
    {
        $this->from('/login')
            ->get('/language/xx')
            ->assertRedirect('/login');

        $this->assertNull(session('locale'));
    }

    public function test_session_locale_is_applied_and_sets_rtl_direction(): void
    {
        $response = $this->withSession(['locale' => 'ar'])->get('/login');

        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
        $this->assertSame('ar', app()->getLocale());
    }

    public function test_default_locale_uses_ltr_direction(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
    }
}
