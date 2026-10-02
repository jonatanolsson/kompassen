<?php

use App\Models\User;

test('design system catalog requires authentication', function () {
    $this->get('/design-system')
        ->assertRedirect(route('login'));
});

test('authenticated users can view the local design system catalog', function () {
    $this->actingAs(User::factory()->create())
        ->get('/design-system')
        ->assertOk()
        ->assertSee(__('Component catalog'))
        ->assertSee(__('Debug'))
        ->assertSee(__('Design System'))
        ->assertSee('x-ui.page-header')
        ->assertSee('x-ui.section')
        ->assertSee(__('Typography and headings'))
        ->assertSee(__('Rich text editor'))
        ->assertSee(__('Audit progress'))
        ->assertSee(__('Separators'))
        ->assertSee(__('Tabs example'))
        ->assertSee(__('Accordion example'))
        ->assertSee(__('Activity panel example.'))
        ->assertSee('<table', false);
});
