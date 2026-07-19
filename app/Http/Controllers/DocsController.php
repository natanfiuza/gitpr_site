<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocsController extends Controller
{
    public function show_document(string $page = 'index')
    {
        $file_path = public_path("content/{$page}.md");

        if (! File::exists($file_path)) {
            abort(404, 'Documento não encontrado.');
        }

        $content   = File::get($file_path);
        $menu_path = public_path('content/menu.json');
        $menu_data = [];

        if (File::exists($menu_path)) {
            $menu_data = json_decode(File::get($menu_path), true);
        }

        $clean_text      = trim(preg_replace('/\s+/', ' ', preg_replace('/[#*`>-]/', '', $content)));
        $seo_description = Str::limit($clean_text, 150);

        return Inertia::render('DocsLayout', [
            'content'         => $content,
            'page_title'      => $page,
            'menu_items'      => $menu_data,
            'seo_description' => $seo_description,
        ]);

    }
}
