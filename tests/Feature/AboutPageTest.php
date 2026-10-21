<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_renders_the_project_presentation(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee("Magie d'Astrata")
            ->assertSee(__('about.features_title'))
            ->assertSee('Sadaka Jariya')
            ->assertSee('composer setup');
    }

    public function test_about_page_is_linked_from_the_navbar_and_footer(): void
    {
        $this->get(route('docs'))
            ->assertOk()
            ->assertSee('href="'.route('about').'"', false);
    }

    public function test_about_page_is_marked_as_current_in_the_navbar(): void
    {
        $html = $this->get(route('about'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<a\s[^>]*href="'.preg_quote(route('about'), '/').'"[^>]*aria-current="page"/',
            $html,
        );
    }
}
