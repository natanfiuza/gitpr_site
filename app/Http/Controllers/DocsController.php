<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocsController extends Controller
{
    /**
     * Render a documentation page with Inertia.
     *
     * Loads the markdown file for the requested page and language (with English
     * fallback), resolves the translated page title from the menu, and passes
     * everything to the DocsLayout Inertia component.
     *
     * @param  Request  $request  The incoming HTTP request. Supports `?lang=` query param.
     * @param  string   $page     The page slug (default: 'index'). Maps to public/content/{page}.md.
     * @return \Inertia\Response
     */
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
            if (($item['type'] ?? '') === 'section') {
                continue;
            }
            if (($item['path'] ?? '') === $page) {
                $page_title = $item['title'];
                break;
            }
        }

        $ui_strings = [
            'en'    => ['on_this_page' => 'On this page', 'menu' => 'Menu', 'contributors' => 'Contributors'],
            'pt_br' => ['on_this_page' => 'Nesta página', 'menu' => 'Menu', 'contributors' => 'Contribuidores'],
            'pt_pt' => ['on_this_page' => 'Nesta página', 'menu' => 'Menu', 'contributors' => 'Contribuidores'],
            'fr'    => ['on_this_page' => 'Sur cette page', 'menu' => 'Menu', 'contributors' => 'Contributeurs'],
            'es'    => ['on_this_page' => 'En esta página', 'menu' => 'Menú', 'contributors' => 'Contribuidores'],
        ];

        // Extract collaborator GitHub usernames from contribuicao.md
        $collaborator_usernames = [];
        $collab_file = public_path('content/contribuicao.md');
        if (File::exists($collab_file)) {
            $collab_content = File::get($collab_file);
            if (preg_match('/:::\s*collaborators\s*\n([\s\S]*?):::/', $collab_content, $collab_match)) {
                $lines = array_filter(array_map('trim', explode("\n", $collab_match[1])));
                foreach ($lines as $line) {
                    if (preg_match('/github\.com\/([a-zA-Z0-9_-]+)\/?$/', $line, $m)) {
                        $collaborator_usernames[] = $m[1];
                    }
                }
            }
        }

        $clean_text      = trim(preg_replace('/\s+/', ' ', preg_replace('/[#*`>-]/', '', $content)));
        $seo_description = Str::limit($clean_text, 150);

        return Inertia::render('DocsLayout', [
            'content'               => $content,
            'current_page'          => $page,
            'page_title'            => $page_title,
            'menu_items'            => $menu_data,
            'collaborator_usernames' => $collaborator_usernames,
            'ui_strings'            => $ui_strings[$lang] ?? $ui_strings['en'],
            'seo_description'       => $seo_description,
            'current_lang'          => $lang,
        ]);

    }

    /**
     * Search across all documentation pages and return matching results as JSON.
     *
     * Iterates over all pages listed in the menu, loads each markdown file in
     * the requested language (with English fallback), and checks whether the
     * search term appears in the content. Matching pages are returned with a
     * snippet of surrounding context.
     *
     * @param  Request  $request  The incoming HTTP request. Supports `?q=` (search term) and `?lang=` query params.
     * @return \Illuminate\Http\JsonResponse
     */
    public function search_content(Request $request)
    {
        $search_term = strtolower($request->query('q', ''));
        $lang        = $request->query('lang', 'en');

        if (empty($search_term)) {
            return response()->json([]);
        }

        $menu_path = public_path('content/menu.json');
        if (! File::exists($menu_path)) {
            return response()->json([]);
        }

        $all_menus = json_decode(File::get($menu_path), true);
        $menu_data = $all_menus[$lang] ?? ($all_menus['en'] ?? []);

        $results = [];

        foreach ($menu_data as $item) {
            if (($item['type'] ?? '') === 'section') {
                continue;
            }
            $page      = $item['path'];
            $base_path = public_path("content/{$page}");
            $file_path = $lang === 'en' ? "{$base_path}.md" : "{$base_path}.{$lang}.md";

            if (! File::exists($file_path) && $lang !== 'en') {
                $file_path = "{$base_path}.md"; // Fallback
            }

            if (File::exists($file_path)) {
                $content       = File::get($file_path);
                $clean_content = trim(preg_replace('/\s+/', ' ', preg_replace('/[#*`>-]/', '', $content)));

                if (str_contains(strtolower($clean_content), $search_term)) {
                    $pos     = stripos($clean_content, $search_term);
                    $start   = max(0, $pos - 40);
                    $snippet = substr($clean_content, $start, 100);

                    $results[] = [
                        'title'   => $item['title'],
                        'path'    => $page,
                        'snippet' => '...' . trim($snippet) . '...',
                    ];
                }
            }
        }

        return response()->json($results);
    }
}
