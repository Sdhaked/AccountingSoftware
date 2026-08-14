<?php

test('the root URL opens the admin area', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
