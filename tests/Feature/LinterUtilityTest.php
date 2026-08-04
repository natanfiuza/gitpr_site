<?php

use App\Models\LinterRuleTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('linter utility page can be rendered', function () {
    // Seed some templates
    LinterRuleTemplate::create([
        'name' => 'test-rule',
        'extensions' => ['php'],
        'regex' => 'die\(',
        'message' => 'No die() allowed',
        'ignore_comments' => true,
    ]);

    $response = $this->get('/linter-utility');

    $response->assertStatus(200);
    // Inertia testing without package can just check if it's rendered properly
    $response->assertSee('test-rule');
});

test('can generate yaml from rules array', function () {
    $rules = [
        [
            'name' => 'check-localhost',
            'extensions' => ['js', 'php'],
            'regex' => 'localhost',
            'message' => 'No localhost',
            'ignore_comments' => true
        ]
    ];

    $response = $this->postJson('/linter-utility/generate', [
        'rules' => $rules
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'yaml']);
             
    $json = $response->json();
    $this->assertTrue($json['success']);
    $this->assertStringContainsString('check-localhost', $json['yaml']);
    $this->assertStringContainsString('localhost', $json['yaml']);
});

test('can parse yaml to rules array', function () {
    $yaml = <<<YAML
rules:
  - name: test-parse
    extensions: ["*"]
    regex: "debugger"
    message: "No debugger"
YAML;

    $response = $this->postJson('/linter-utility/parse', [
        'yaml' => $yaml
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'rules']);
             
    $json = $response->json();
    $this->assertTrue($json['success']);
    $this->assertCount(1, $json['rules']);
    $this->assertEquals('test-parse', $json['rules'][0]['name']);
});
