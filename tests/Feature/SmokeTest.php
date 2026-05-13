<?php

it('redirects root to admin panel', function () {
    $this->get('/')->assertRedirect('/admin');
});

it('serves the public association registration page', function () {
    $this->get(route('register.association.show'))
        ->assertOk()
        ->assertSee(__('common.register_association'), false);
});

it('serves the admin login page', function () {
    $this->get('/admin/login')->assertOk();
});

it('serves the excellence login page', function () {
    $this->get('/excellence/login')->assertOk();
});

it('serves the donor login page', function () {
    $this->get('/donor/login')->assertOk();
});

it('serves the consultant login page', function () {
    $this->get('/consultant/login')->assertOk();
});

it('serves the association login page', function () {
    $this->get('/association/login')->assertOk();
});
