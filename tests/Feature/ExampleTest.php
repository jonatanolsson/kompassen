<?php

use App\Models\User;

it('redirects guests to login from the root route', function () {
    $this->get('/')
        ->assertRedirect(route('login'));
});

it('redirects authenticated users to dashboard from the root route', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
