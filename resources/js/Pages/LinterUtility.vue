<template>
  <div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-gitpr_dark dark:text-gitpr_text font-sans selection:bg-gitpr_primary selection:text-white flex flex-col">
    <Head :title="t.page_title" />

    <header class="flex-shrink-0 h-14 bg-white border-b border-slate-200 dark:bg-gitpr_dark dark:border-gitpr_dark_border flex items-center px-4 lg:px-6 z-50 transition-colors duration-300">
      <div class="w-full max-w-7xl mx-auto flex items-center justify-between">
        <div class="flex items-center gap-2">
            <Link href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                <span class="font-bold text-xl text-slate-900 dark:text-gitpr_text transition-colors duration-300">GitPR</span>
                <span class="text-xs text-gitpr_cyan_dark hidden sm:inline">[ CLI ]</span>
            </Link>
            <span class="ml-2 text-sm font-medium text-slate-500 dark:text-slate-400">| Linter Utility</span>
        </div>
        <div class="flex items-center gap-4">
          <Link :href="'/index' + (current_lang !== 'en' ? '?lang=' + current_lang : '')" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-gitpr_primary transition-colors">
            {{ t.back_to_docs }}
          </Link>
          <ThemeToggle />
          <LanguageSelector :current_lang="current_lang" />
        </div>
      </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex gap-8 w-full">
      
      <!-- Left Panel: Rule Templates -->
      <aside class="w-1/4 flex flex-col gap-4">
        <div class="bg-white dark:bg-slate-800/50 rounded-xl p-4 border border-slate-200 dark:border-slate-700 shadow-sm">
          <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gitpr_primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            {{ t.templates }}
          </h2>
          <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ t.templates_desc }}</p>
          <div class="space-y-3">
            <div v-for="template in templates" :key="template.id" class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:border-gitpr_primary transition-colors cursor-pointer" @click="loadTemplate(template)">
              <div class="font-semibold text-sm text-slate-800 dark:text-slate-200">{{ template.name }}</div>
              <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ template.description }}</div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Center Panel: Rule Builder -->
      <section class="w-2/4 flex flex-col gap-6">
        
        <div class="bg-white dark:bg-slate-800/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold">{{ t.rule_builder }}</h2>
            <div class="flex gap-2">
              <button @click="triggerFileUpload" class="px-3 py-1.5 text-sm rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors font-medium">{{ t.import_yaml }}</button>
              <button @click="exportYaml" class="px-3 py-1.5 text-sm rounded-lg bg-gitpr_primary text-white hover:bg-blue-600 transition-colors font-medium shadow-md shadow-blue-500/20">{{ t.export_yaml }}</button>
              <input type="file" ref="fileInput" class="hidden" accept=".yml,.yaml" @change="handleFileUpload">
            </div>
          </div>

          <form @submit.prevent class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">{{ t.rule_name }}</label>
              <input v-model="form.name" type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-transparent dark:bg-slate-900/50 focus:ring-gitpr_primary focus:border-gitpr_primary sm:text-sm" :placeholder="t.rule_name_placeholder">
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">{{ t.regex_label }}</label>
              <div class="relative">
                <input v-model="form.regex" @input="testRegex" type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-transparent dark:bg-slate-900/50 focus:ring-gitpr_primary focus:border-gitpr_primary sm:text-sm font-mono text-gitpr_cyan_dark" placeholder="\bTODO\b:">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">{{ t.violation_msg }}</label>
              <input v-model="form.message" type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-transparent dark:bg-slate-900/50 focus:ring-gitpr_primary focus:border-gitpr_primary sm:text-sm" :placeholder="t.violation_placeholder">
              <p class="text-xs text-slate-500 mt-1">{{ t.available_vars }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">{{ t.extensions }}</label>
                <input v-model="extensionsString" type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-transparent dark:bg-slate-900/50 focus:ring-gitpr_primary focus:border-gitpr_primary sm:text-sm" :placeholder="t.extensions_placeholder">
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">{{ t.ignore_paths }}</label>
                <input v-model="ignorePathsString" type="text" class="w-full rounded-lg border-slate-300 dark:border-slate-600 bg-transparent dark:bg-slate-900/50 focus:ring-gitpr_primary focus:border-gitpr_primary sm:text-sm" :placeholder="t.ignore_paths_placeholder">
              </div>
            </div>

            <div class="flex items-center mt-2">
              <input v-model="form.ignore_comments" id="ignore_comments" type="checkbox" class="h-4 w-4 text-gitpr_primary focus:ring-gitpr_primary border-gray-300 rounded">
              <label for="ignore_comments" class="ml-2 block text-sm text-slate-700 dark:text-slate-300">
                {{ t.ignore_comments }}
              </label>
            </div>
            
            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 mt-4">
                <button @click="saveRuleToList" class="w-full py-2 bg-slate-800 dark:bg-slate-700 text-white rounded-lg font-medium hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                    {{ t.add_update_rule }}
                </button>
            </div>
          </form>
        </div>

        <!-- Current Rules List -->
        <div class="bg-white dark:bg-slate-800/50 rounded-xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm" v-if="rules.length > 0">
            <h2 class="text-lg font-bold mb-4">{{ t.rules_in_project }} ({{ rules.length }})</h2>
            <div class="space-y-2">
                <div v-for="(rule, index) in rules" :key="index" class="flex justify-between items-center p-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <div>
                        <span class="font-medium text-sm text-gitpr_primary">{{ rule.name }}</span>
                        <span class="text-xs text-slate-500 ml-2 font-mono truncate max-w-[200px] inline-block align-bottom">{{ rule.regex }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="editRule(index)" class="text-slate-400 hover:text-gitpr_cyan_light transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                        <button @click="removeRule(index)" class="text-slate-400 hover:text-red-500 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                </div>
            </div>
        </div>

      </section>

      <!-- Right Panel: Regex Tester -->
      <aside class="w-1/4 flex flex-col gap-4">
        <div class="bg-slate-900 rounded-xl p-4 border border-slate-800 shadow-xl overflow-hidden flex flex-col h-[500px]">
          <h2 class="text-lg font-bold mb-2 text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-gitpr_cyan_light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
            {{ t.regex_tester }}
          </h2>
          <p class="text-xs text-slate-400 mb-3">{{ t.tester_desc }}</p>
          
          <div class="relative flex-grow flex flex-col">
            <textarea 
                v-model="testText" 
                @input="testRegex"
                class="w-full flex-grow bg-slate-950 text-slate-300 font-mono text-sm p-3 rounded-lg border border-slate-700 focus:ring-1 focus:ring-gitpr_cyan_light focus:border-gitpr_cyan_light resize-none absolute inset-0 z-10 opacity-70"
                :placeholder="t.tester_placeholder"
            ></textarea>
            
            <!-- Highlight overlay -->
            <div class="absolute inset-0 z-0 p-3 font-mono text-sm whitespace-pre-wrap overflow-hidden" v-html="highlightedText"></div>
          </div>

          <div class="mt-4 p-3 rounded bg-slate-800 border border-slate-700">
            <div class="text-sm text-slate-300 font-medium mb-1">{{ t.regex_status }}</div>
            <div v-if="regexError" class="text-xs text-red-400 break-all">{{ regexError }}</div>
            <div v-else-if="matchesCount > 0" class="text-xs text-green-400 font-bold flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ matchesCount }} {{ t.matches_found }}
            </div>
            <div v-else class="text-xs text-slate-500">{{ t.no_matches }}</div>
          </div>
        </div>
      </aside>

    </main>

    <!-- Alert Modal -->
    <Modal :show="showModal" @close="showModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-slate-100 mb-4">{{ modalTitle }}</h2>
        <p class="text-sm text-slate-300">{{ modalMessage }}</p>
        <div class="mt-6 flex justify-end">
          <button @click="showModal = false" class="px-4 py-2 bg-gitpr_primary text-white rounded hover:bg-gitpr_primary/90 transition-colors text-sm font-semibold shadow-md">
            OK
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, watch, shallowRef, markRaw } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { parseDocument, Document, isSeq, isMap } from 'yaml';
import Modal from '@/Components/Modal.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    templates: Array,
    current_lang: {
        type: String,
        default: 'en'
    }
});

const translations = {
    en: {
        back_to_docs: "Back to Documentation",
        templates: "Templates",
        templates_desc: "Add ready-to-use, optimized rules to your linter.",
        rule_builder: "Rule Builder",
        import_yaml: "Import YAML",
        export_yaml: "Export YAML",
        rule_name: "Rule Name",
        rule_name_placeholder: "e.g. check-localhost",
        regex_label: "Regex (Regular Expression)",
        violation_msg: "Violation Message",
        violation_placeholder: "🚨 Error on line {line_number} of file {file_name}",
        available_vars: "Available variables: {file_name}, {line_number}",
        extensions: "Extensions (comma-separated)",
        extensions_placeholder: "php, js, ts",
        ignore_paths: "Ignore Paths (comma-separated)",
        ignore_paths_placeholder: "vendor/*, tests/*",
        ignore_comments: "Ignore Comments",
        add_update_rule: "Add/Update in Rules List",
        rules_in_project: "Project Rules",
        regex_tester: "Regex Tester",
        tester_desc: "Paste your code below to test matches in real-time.",
        tester_placeholder: "Paste source code here...",
        regex_status: "Regex Status:",
        matches_found: "match(es) found",
        no_matches: "No matches.",
        fill_fields: "Fill in Name, Regex, and Message.",
        required_fields: "Required Fields",
        invalid_rules_key: "The file does not contain a valid 'rules' key.",
        invalid_format: "Invalid Format",
        yaml_parse_error: "Error parsing YAML: ",
        read_error: "Read Error",
        add_at_least_one: "Add at least one rule to export.",
        no_rules: "No Rules",
        fatal_export_error: "Fatal error exporting: ",
        export_error: "Export Error",
        catastrophic_warn: "⚠️ Warning: Regex contains '.*' or repetitive patterns that may cause slowness (Catastrophic Backtracking). Prefer anchors (\\b) or negated classes ([^...])."
    },
    pt_br: {
        back_to_docs: "Voltar para a Documentação",
        templates: "Templates",
        templates_desc: "Adicione regras prontas e otimizadas ao seu linter.",
        rule_builder: "Construtor de Regra",
        import_yaml: "Importar YAML",
        export_yaml: "Exportar YAML",
        rule_name: "Nome da Regra",
        rule_name_placeholder: "ex: check-localhost",
        regex_label: "Regex (Expressão Regular)",
        violation_msg: "Mensagem de Violação",
        violation_placeholder: "🚨 Erro na linha {line_number} do arquivo {file_name}",
        available_vars: "Variáveis disponíveis: {file_name}, {line_number}",
        extensions: "Extensões (separadas por vírgula)",
        extensions_placeholder: "php, js, ts",
        ignore_paths: "Ignorar Paths (separados por vírgula)",
        ignore_paths_placeholder: "vendor/*, tests/*",
        ignore_comments: "Ignorar Comentários",
        add_update_rule: "Adicionar/Atualizar na Lista de Regras",
        rules_in_project: "Regras no Projeto",
        regex_tester: "Regex Tester",
        tester_desc: "Cole seu código abaixo para testar o \"match\" em tempo real.",
        tester_placeholder: "Cole o código fonte aqui...",
        regex_status: "Status do Regex:",
        matches_found: "correspondência(s) encontrada(s)",
        no_matches: "Nenhuma correspondência.",
        fill_fields: "Preencha Nome, Regex e Mensagem.",
        required_fields: "Campos Obrigatórios",
        invalid_rules_key: "O arquivo não possui uma chave 'rules' válida.",
        invalid_format: "Formato Inválido",
        yaml_parse_error: "Erro ao parsear o YAML: ",
        read_error: "Erro de Leitura",
        add_at_least_one: "Adicione pelo menos uma regra para exportar.",
        no_rules: "Nenhuma Regra",
        fatal_export_error: "Erro fatal ao exportar: ",
        export_error: "Erro de Exportação",
        catastrophic_warn: "⚠️ Cuidado: Regex contém \".*\" ou padrões repetitivos que podem causar Lentidão (Catastrophic Backtracking). Prefira âncoras (\\b) ou classes negadas ([^...])."
    },
    pt_pt: {
        back_to_docs: "Voltar para a Documentação",
        templates: "Modelos",
        templates_desc: "Adicione regras prontas e otimizadas ao seu linter.",
        rule_builder: "Construtor de Regras",
        import_yaml: "Importar YAML",
        export_yaml: "Exportar YAML",
        rule_name: "Nome da Regra",
        rule_name_placeholder: "ex: check-localhost",
        regex_label: "Regex (Expressão Regular)",
        violation_msg: "Mensagem de Violação",
        violation_placeholder: "🚨 Erro na linha {line_number} do ficheiro {file_name}",
        available_vars: "Variáveis disponíveis: {file_name}, {line_number}",
        extensions: "Extensões (separadas por vírgula)",
        extensions_placeholder: "php, js, ts",
        ignore_paths: "Ignorar Caminhos (separados por vírgula)",
        ignore_paths_placeholder: "vendor/*, tests/*",
        ignore_comments: "Ignorar Comentários",
        add_update_rule: "Adicionar/Atualizar na Lista de Regras",
        rules_in_project: "Regras no Projeto",
        regex_tester: "Testador de Regex",
        tester_desc: "Cole o seu código abaixo para testar a correspondência em tempo real.",
        tester_placeholder: "Cole o código fonte aqui...",
        regex_status: "Estado do Regex:",
        matches_found: "correspondência(s) encontrada(s)",
        no_matches: "Nenhuma correspondência.",
        fill_fields: "Preencha Nome, Regex e Mensagem.",
        required_fields: "Campos Obrigatórios",
        invalid_rules_key: "O ficheiro não possui uma chave 'rules' válida.",
        invalid_format: "Formato Inválido",
        yaml_parse_error: "Erro ao parsear o YAML: ",
        read_error: "Erro de Leitura",
        add_at_least_one: "Adicione pelo menos uma regra para exportar.",
        no_rules: "Nenhuma Regra",
        fatal_export_error: "Erro fatal ao exportar: ",
        export_error: "Erro de Exportação",
        catastrophic_warn: "⚠️ Cuidado: Regex contém \".*\" ou padrões repetitivos que podem causar Lentidão (Catastrophic Backtracking). Prefira âncoras (\\b) ou classes negadas ([^...])."
    },
    fr: {
        back_to_docs: "Retour à la Documentation",
        templates: "Modèles",
        templates_desc: "Ajoutez des règles optimisées prêtes à l'emploi à votre linter.",
        rule_builder: "Constructeur de Règles",
        import_yaml: "Importer YAML",
        export_yaml: "Exporter YAML",
        rule_name: "Nom de la Règle",
        rule_name_placeholder: "ex : check-localhost",
        regex_label: "Regex (Expression Régulière)",
        violation_msg: "Message de Violation",
        violation_placeholder: "🚨 Erreur à la ligne {line_number} du fichier {file_name}",
        available_vars: "Variables disponibles : {file_name}, {line_number}",
        extensions: "Extensions (séparées par des virgules)",
        extensions_placeholder: "php, js, ts",
        ignore_paths: "Ignorer les Chemins (séparés par des virgules)",
        ignore_paths_placeholder: "vendor/*, tests/*",
        ignore_comments: "Ignorer les Commentaires",
        add_update_rule: "Ajouter/Mettre à jour dans la Liste de Règles",
        rules_in_project: "Règles du Projet",
        regex_tester: "Testeur de Regex",
        tester_desc: "Collez votre code ci-dessous pour tester la correspondance en temps réel.",
        tester_placeholder: "Collez le code source ici...",
        regex_status: "Statut du Regex :",
        matches_found: "correspondance(s) trouvée(s)",
        no_matches: "Aucune correspondance.",
        fill_fields: "Veuillez remplir Nom, Regex et Message.",
        required_fields: "Champs Obligatoires",
        invalid_rules_key: "Le fichier ne contient pas de clé 'rules' valide.",
        invalid_format: "Format Invalide",
        yaml_parse_error: "Erreur lors de l'analyse du YAML : ",
        read_error: "Erreur de Lecture",
        add_at_least_one: "Ajoutez au moins une règle à exporter.",
        no_rules: "Aucune Règle",
        fatal_export_error: "Erreur fatale lors de l'exportation : ",
        export_error: "Erreur d'Exportation",
        catastrophic_warn: "⚠️ Attention : Le Regex contient '.*' ou des motifs répétitifs pouvant causer une lenteur (Catastrophic Backtracking). Préférez les ancres (\\b) ou les classes niées ([^...])."
    },
    es: {
        back_to_docs: "Volver a la Documentación",
        templates: "Plantillas",
        templates_desc: "Añada reglas optimizadas listas para usar a su linter.",
        rule_builder: "Constructor de Reglas",
        import_yaml: "Importar YAML",
        export_yaml: "Exportar YAML",
        rule_name: "Nombre de la Regla",
        rule_name_placeholder: "ej: check-localhost",
        regex_label: "Regex (Expresión Regular)",
        violation_msg: "Mensaje de Violación",
        violation_placeholder: "🚨 Error en la línea {line_number} del archivo {file_name}",
        available_vars: "Variables disponibles: {file_name}, {line_number}",
        extensions: "Extensiones (separadas por comas)",
        extensions_placeholder: "php, js, ts",
        ignore_paths: "Ignorar Rutas (separadas por comas)",
        ignore_paths_placeholder: "vendor/*, tests/*",
        ignore_comments: "Ignorar Comentarios",
        add_update_rule: "Añadir/Actualizar en la Lista de Reglas",
        rules_in_project: "Reglas del Proyecto",
        regex_tester: "Probador de Regex",
        tester_desc: "Pegue su código abajo para probar la coincidencia en tiempo real.",
        tester_placeholder: "Pegue el código fuente aquí...",
        regex_status: "Estado de Regex:",
        matches_found: "coincidencia(s) encontrada(s)",
        no_matches: "Sin coincidencias.",
        fill_fields: "Rellene Nombre, Regex y Mensaje.",
        required_fields: "Campos Obligatorios",
        invalid_rules_key: "El archivo no contiene una clave 'rules' válida.",
        invalid_format: "Formato Inválido",
        yaml_parse_error: "Error al analizar el YAML: ",
        read_error: "Error de Lectura",
        add_at_least_one: "Añada al menos una regla para exportar.",
        no_rules: "Sin Reglas",
        fatal_export_error: "Error fatal al exportar: ",
        export_error: "Error de Exportación",
        catastrophic_warn: "⚠️ Atención: La Regex contiene '.*' o patrones repetitivos que pueden causar lentitud (Catastrophic Backtracking). Prefiera anclas (\\b) o clases negadas ([^...])."
    }
};

const t = computed(() => translations[props.current_lang] || translations.en);

// Modal State
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');

const showAlert = (message, title = 'Aviso') => {
    modalMessage.value = message;
    modalTitle.value = title;
    showModal.value = true;
};

// App State
const rules = ref([]);
const yamlDoc = shallowRef(null); // Armazena a AST do YAML original para manter comentários
const fileInput = ref(null);
const currentEditingIndex = ref(-1);

const form = ref({
    name: '',
    regex: '',
    extensions: ['*'],
    message: '',
    ignore_comments: false,
    ignore_paths: []
});

const testText = ref('// Exemplo de código com erro\nconsole.log("Debug message");\n\nconst route = "http://localhost:8000";\n\n// TODO: Fix this later');
const regexError = ref('');
const matchesCount = ref(0);

// Helpers for input arrays
const extensionsString = computed({
    get: () => form.value.extensions.join(', '),
    set: (val) => form.value.extensions = val.split(',').map(s => s.trim()).filter(s => s)
});

const ignorePathsString = computed({
    get: () => (form.value.ignore_paths || []).join(', '),
    set: (val) => form.value.ignore_paths = val.split(',').map(s => s.trim()).filter(s => s)
});

// Regex Tester Logic
const highlightedText = computed(() => {
    if (!form.value.regex || regexError.value || matchesCount.value === 0) return escapeHtml(testText.value);
    
    try {
        let pattern = form.value.regex;
        let flags = 'g';
        
        // Adaptação: Transforma a flag inline (?i) do Python/PCRE em flag do Javascript 'i'
        if (pattern.includes('(?i)')) {
            pattern = pattern.replace('(?i)', '');
            flags += 'i';
        }
        
        const re = new RegExp(pattern, flags);
        let result = escapeHtml(testText.value);
        
        // This is a simplified highlighter
        result = result.replace(re, (match) => {
            return `<span class="bg-gitpr_cyan_light/30 text-gitpr_cyan_light font-bold rounded px-0.5 border-b border-gitpr_cyan_light">${escapeHtml(match)}</span>`;
        });
        
        return result;
    } catch (e) {
        return escapeHtml(testText.value);
    }
});

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

const testRegex = () => {
    if (!form.value.regex) {
        regexError.value = '';
        matchesCount.value = 0;
        return;
    }

    try {
        let pattern = form.value.regex;
        let flags = 'g';
        
        // Adaptação: Transforma a flag inline (?i) do Python/PCRE em flag do Javascript 'i'
        if (pattern.includes('(?i)')) {
            pattern = pattern.replace('(?i)', '');
            flags += 'i';
        }
        
        // Test compile
        const re = new RegExp(pattern, flags);
        regexError.value = '';
        
        // Find matches
        const matches = testText.value.match(re);
        matchesCount.value = matches ? matches.length : 0;
        
        // Warn about catastrophic backtracking patterns
        if (form.value.regex.includes('.*') || form.value.regex.match(/(\w+)\+\+/)) {
            regexError.value = t.value.catastrophic_warn;
        }
    } catch (e) {
        regexError.value = e.message;
        matchesCount.value = 0;
    }
};

watch(() => form.value.regex, testRegex);

// Interactions
const loadTemplate = (template) => {
    form.value = {
        name: template.name,
        regex: template.regex,
        extensions: template.extensions || ['*'],
        message: template.message,
        ignore_comments: !!template.ignore_comments,
        ignore_paths: template.ignore_paths || []
    };
    currentEditingIndex.value = -1;
    testRegex();
};

const saveRuleToList = () => {
    if (!form.value.name || !form.value.regex || !form.value.message) {
        showAlert(t.value.fill_fields, t.value.required_fields);
        return;
    }
    
    const ruleObj = JSON.parse(JSON.stringify(form.value));
    
    // Cleanup empty paths
    if (ruleObj.ignore_paths && ruleObj.ignore_paths.length === 0) {
        delete ruleObj.ignore_paths;
    }
    
    if (currentEditingIndex.value >= 0) {
        rules.value[currentEditingIndex.value] = ruleObj;
    } else {
        rules.value.push(ruleObj);
    }
    
    // Clear form
    form.value = {
        name: '', regex: '', extensions: ['*'], message: '', ignore_comments: false, ignore_paths: []
    };
    currentEditingIndex.value = -1;
};

const editRule = (index) => {
    form.value = JSON.parse(JSON.stringify(rules.value[index]));
    if (!form.value.ignore_paths) form.value.ignore_paths = [];
    currentEditingIndex.value = index;
    testRegex();
};

const removeRule = (index) => {
    rules.value.splice(index, 1);
};

const triggerFileUpload = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            // Usa o 'yaml' (AST) para manter comentários na memória
            const doc = parseDocument(e.target.result);
            yamlDoc.value = markRaw(doc);
            const parsedRules = doc.get('rules');
            
            if (parsedRules && parsedRules.items) {
                rules.value = parsedRules.toJSON();
            } else {
                showAlert(t.value.invalid_rules_key, t.value.invalid_format);
            }
        } catch (err) {
            showAlert(t.value.yaml_parse_error + err.message, t.value.read_error);
        }
    };
    reader.readAsText(file);
    
    // reset input
    event.target.value = null;
};

const exportYaml = () => {
    if (rules.value.length === 0) {
        showAlert(t.value.add_at_least_one, t.value.no_rules);
        return;
    }

    try {
        let doc = yamlDoc.value;
        const rawRules = JSON.parse(JSON.stringify(rules.value));
        
        if (!doc) {
            // Se não fez upload de um arquivo, cria um do zero
            doc = new Document();
            doc.set('rules', rawRules);
        } else {
            // Se já tem AST, sincroniza as regras para MANTER COMENTÁRIOS!
            const seq = doc.get('rules');
            if (isSeq(seq)) {
                const newItems = rawRules.map(ruleObj => {
                    // Procura o nó existente pelo nome para manter os blocos de comentário acima dele
                    const existingNode = seq.items.find(item => isMap(item) && item.get('name') === ruleObj.name);
                    let newNode;
                    if (existingNode) {
                        // Atualiza as propriedades no nó existente
                        for (const key in ruleObj) {
                            existingNode.set(key, ruleObj[key]);
                        }
                        // Limpa chaves que foram removidas (ex: ignore_paths)
                        for (const item of existingNode.items) {
                            if (!ruleObj.hasOwnProperty(item.key.value)) {
                                existingNode.delete(item.key.value);
                            }
                        }
                        newNode = existingNode;
                    } else {
                        // Cria um novo nó para a nova regra adicionada
                        newNode = doc.createNode(ruleObj);
                    }
                    return newNode;
                });
                seq.items = newItems;
            } else {
                doc.set('rules', rules.value);
            }
        }

        const yamlString = doc.toString();
        
        // Define charset utf-8 para evitar problemas de codificação e garantir que abra como texto puro.
        const blob = new Blob([yamlString], { type: 'text/plain;charset=utf-8' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = '.gitpr.linter.yml';
        a.click();
        window.URL.revokeObjectURL(url);
    } catch (e) {
        showAlert(t.value.fatal_export_error + e.message, t.value.export_error);
        console.error(e);
    }
};
</script>

<style scoped>
/* Scoped styles if needed */
</style>
