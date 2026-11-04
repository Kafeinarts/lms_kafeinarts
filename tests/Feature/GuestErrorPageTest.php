<?php

test('unsigned storage file request renders the 403 error page instead of a server error', function () {
    $this->get('/storage/missing-file.jpg')
        ->assertStatus(403)
        ->assertSee('403');
});
