<?php

it('redirects browser 404 responses to the admin dashboard', function () {
    $response = $this->get('/this-page-does-not-exist', [
        'Accept' => 'text/html',
        'Sec-Fetch-Dest' => 'document',
    ]);

    $response->assertRedirect(route('admin.dashboard.index'));
});

it('keeps API 404 responses as JSON', function () {
    $this->getJson('/api/this-endpoint-does-not-exist')
        ->assertNotFound()
        ->assertJsonPath('message', 'The route api/this-endpoint-does-not-exist could not be found.');
});

it('does not redirect non HTML 404 responses', function () {
    $this->get('/missing-image.png', [
        'Accept' => 'image/avif,image/webp,image/*,*/*;q=0.8',
        'Sec-Fetch-Dest' => 'image',
    ])->assertNotFound();
});

it('does not redirect background fetch 404 responses', function () {
    $this->get('/missing-page-fragment', [
        'Accept' => '*/*',
        'Sec-Fetch-Dest' => 'empty',
    ])->assertNotFound();
});
