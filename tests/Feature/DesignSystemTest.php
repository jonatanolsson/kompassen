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
        ->assertSee('x-ui.page-header')
        ->assertSee('x-ui.section')
        ->assertSee('<table', false);
});
