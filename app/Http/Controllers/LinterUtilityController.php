<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LinterRuleTemplate;
use Symfony\Component\Yaml\Yaml;

class LinterUtilityController extends Controller
{
    /**
     * Display the Linter Utility tool page.
     */
    public function index(Request $request)
    {
        $lang = $request->query('lang', 'en');
        $validLangs = ['en', 'pt_br', 'pt_pt', 'fr', 'es'];
        if (! in_array($lang, $validLangs)) {
            $lang = 'en';
        }

        $templates = LinterRuleTemplate::all();

        return Inertia::render('LinterUtility', [
            'templates'    => $templates,
            'current_lang' => $lang,
        ]);
    }

    /**
     * Parse an uploaded YAML file and return JSON rules.
     * This is useful if we want the backend to parse the YAML 
     * instead of relying strictly on frontend parsing, but for
     * this implementation the frontend `js-yaml` will likely handle it.
     * Including as a fallback.
     */
    public function parseYaml(Request $request)
    {
        $request->validate([
            'yaml' => 'required|string'
        ]);

        try {
            $parsed = Yaml::parse($request->input('yaml'));
            return response()->json([
                'success' => true,
                'rules' => $parsed['rules'] ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid YAML format: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate YAML from JSON rules.
     */
    public function generateYaml(Request $request)
    {
        $request->validate([
            'rules' => 'required|array'
        ]);

        try {
            $yaml = Yaml::dump([
                'rules' => $request->input('rules')
            ], 4, 2);

            return response()->json([
                'success' => true,
                'yaml' => $yaml
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating YAML: ' . $e->getMessage()
            ], 500);
        }
    }
}
