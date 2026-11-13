<?php

it('shows the information pages and links them in the footer', function (string $route, string $title) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee($title);

    $this->get(route('home'))->assertSee(route($route));
})->with([
    ['pages.contact', 'Contactos'],
    ['pages.returns', 'Trocas e devoluções'],
    ['pages.privacy', 'Política de privacidade'],
    ['pages.terms', 'Termos e condições'],
]);
