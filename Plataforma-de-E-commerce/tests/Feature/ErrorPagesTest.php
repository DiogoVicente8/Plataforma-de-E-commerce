<?php

use App\Models\User;

it('shows a Portuguese 404 page for an unknown route', function () {
    $this->get('/pagina-inexistente')->assertNotFound()->assertSee('Página não encontrada');
});

it('shows a Portuguese 403 page to a customer opening admin', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')->assertForbidden()->assertSee('Não tens permissão');
});
