<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocsController extends Controller
{
    public function show_document(Request $request, string $page = 'index')
    {
        $lang = $request->query('lang', 'en');

       

        $base_path = public_path("content/{$page}");
        $file_path = $lang === 'en' ? "{$base_path}.md" : "{$base_path}.{$lang}.md";

        if (! File::exists($file_path) && $lang !== 'en') {
            $file_path = "{$base_path}.md"; // Fallback
        }

        if (! File::exists($file_path)) {
            abort(404, 'Documento não encontrado.');
        }

        $content   = File::get($file_path);
        $menu_path = public_path('content/menu.json');
        $menu_data = [];

        if (File::exists($menu_path)) {
            $all_menus = json_decode(File::get($menu_path), true);
            if (is_array($all_menus)) {
                $menu_data = $all_menus[$lang] ?? ($all_menus['en'] ?? []);
            }
        }

        // Look up the translated page title from the menu
        $page_title = $page; // fallback to filename
        foreach ($menu_data as $item) {
            if (($item['path'] ?? '') === $page) {
                $page_title = $item['title'];
                break;
            }
        }

        $ui_strings = [
            'en'    => ['on_this_page' => 'On this page', 'menu' => 'Menu'],
            'pt_br' => ['on_this_page' => 'Nesta página', 'menu' => 'Menu'],
            'pt_pt' => ['on_this_page' => 'Nesta página', 'menu' => 'Menu'],
            'fr'    => ['on_this_page' => 'Sur cette page', 'menu' => 'Menu'],
            'es'    => ['on_this_page' => 'En esta página', 'menu' => 'Menú'],
        ];

        $clean_text      = trim(preg_replace('/\s+/', ' ', preg_replace('/[#*`>-]/', '', $content)));
        $seo_description = Str::limit($clean_text, 150);

        return Inertia::render('DocsLayout', [
            'content'         => $content,
            'current_page'    => $page,
            'page_title'      => $page_title,
            'menu_items'      => $menu_data,
            'ui_strings'      => $ui_strings[$lang] ?? $ui_strings['en'],
            'seo_description' => $seo_description,
            'current_lang'    => $lang,
        ]);

    }
}
