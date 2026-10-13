<?php

it('does not expose local password recovery', function () {
    $this->get('/forgot-password')->assertNotFound();
    $this->get('/reset-password/example')->assertNotFound();
});
