<?php

test('login page works in a real browser', function () {
    visit('/login')
        ->assertSee(__('Log in'))
        ->assertNoJavaScriptErrors();
});
