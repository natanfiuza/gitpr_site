<?php

test('search returns JSON results for terms present in the documentation', function () {
    $response = $this->get('/search?q=git&lang=en');

    $response->assertOk();
    $response->assertJsonStructure([['title', 'path', 'snippet']]);
    expect($response->json())->not->toBeEmpty();
});

test('search returns JSON for accented content (pt_br) without utf-8 errors', function () {
    // Regressão: substr() por bytes dividia caracteres multibyte no snippet,
    // gerava UTF-8 inválido e o json_encode lançava 500.
    $response = $this->get('/search?q=git&lang=pt_br');

    $response->assertOk();
    $response->assertJsonStructure([['title', 'path', 'snippet']]);
    expect($response->json())->not->toBeEmpty();
});

test('search returns empty array when nothing matches', function () {
    $this->get('/search?q=zzzztermo_inexistente')
        ->assertOk()
        ->assertExactJson([]);
});

test('search endpoint is not reachable under the /api prefix', function () {
    // O edge da hospedagem (hcdn) responde 307 em loop para /api/* — a rota de
    // busca foi movida para /search. Este teste trava a regressão se alguém
    // reintroduzir /api/search: cairia na catch-all e viraria 404.
    $this->get('/api/search?q=git')
        ->assertNotFound();
});
