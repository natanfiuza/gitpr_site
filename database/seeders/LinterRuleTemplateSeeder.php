<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinterRuleTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'check-php-debug',
                'extensions' => json_encode(['php']),
                'regex' => '\b(?:dd|dump|var_dump|print_r|die|exit)\s*\(',
                'message' => '🚨 Código de debug esquecido no arquivo {file_name} (Linha {line_number}). Remova antes de enviar.',
                'ignore_comments' => true,
                'ignore_paths' => json_encode(['tests/*']),
                'description' => 'Bloqueia funções de debug comuns do PHP, usando âncoras \b para evitar catastrophic backtracking.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'check-js-console',
                'extensions' => json_encode(['js', 'ts', 'vue']),
                'regex' => '\bconsole\.(?:log|debug|info|warn|error)\([^)]*\)',
                'message' => '🚨 Console.log detectado em {file_name}:{line_number}.',
                'ignore_comments' => true,
                'ignore_paths' => null,
                'description' => 'Bloqueia console.log usando classe negada [^)]* em vez de .* para alta performance.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'block-fixed-ip',
                'extensions' => json_encode(['*']),
                'regex' => '\b(?:\d{1,3}\.){3}\d{1,3}\b',
                'message' => '⚠️ Endereço IP fixo detectado ({file_name}:{line_number}). Use variáveis de ambiente.',
                'ignore_comments' => false,
                'ignore_paths' => null,
                'description' => 'Detecta IPs fixos como 192.168.0.1. Usa grupos não-capturantes (?:) e âncoras para máxima velocidade.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'find-todos',
                'extensions' => json_encode(['*']),
                'regex' => '(?i)\b(?:TODO|FIXME)\b:',
                'message' => '⚠️ Tarefa pendente encontrada ({file_name}:{line_number}).',
                'ignore_comments' => false,
                'ignore_paths' => null,
                'description' => 'Alerta sobre TODOs ou FIXMEs não resolvidos.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('linter_rule_templates')->insertOrIgnore($templates);
    }
}
