<?php

it('uses the same Google entry point for registration', function () {
    $this->get('/register')->assertRedirect('/login');
});
