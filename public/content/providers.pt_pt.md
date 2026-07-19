# Fornecedores IA — Arquitetura Multi-Modelo

O GitPR é **agnóstico de IA**: não está vinculado a um único modelo ou fornecedor. Durante a configuração, escolha o seu motor padrão. Alterne a qualquer momento com uma única flag.

---

## Fornecedores Suportados

### Google Gemini

O fornecedor padrão e recomendado. Rápido, económico e com suporte nativo.

```bash
# Definir como padrão em ~/.gitpr/.env
GITPR_PROVIDER=gemini
GEMINI_API_MODEL=gemini-pro-latest

# Ou usar uma vez
gitpr --provider gemini
```

**Modelo padrão:** `gemini-pro-latest`

**Obtenha a sua chave API:** [Google AI Studio](https://aistudio.google.com/apikey)

---

### DeepSeek

Alternativa poderosa com excelente capacidade de análise de código. Usa o SDK compatível com OpenAI.

```bash
# Definir como padrão em ~/.gitpr/.env
GITPR_PROVIDER=deepseek
DEEPSEEK_API_MODEL=deepseek-v4-pro

# Ou usar uma vez
gitpr --provider deepseek
```

**Modelo padrão:** `deepseek-v4-pro`

**Obtenha a sua chave API:** [DeepSeek Platform](https://platform.deepseek.com/api_keys)

---

### Ollama (Local)

Execute modelos **totalmente offline** — sem internet, sem chaves API, sem quotas.

```bash
# Definir como padrão em ~/.gitpr/.env
GITPR_PROVIDER=ollama

# Ou usar uma vez
gitpr --provider ollama
```

O Ollama usa o formato de API compatível com OpenAI, tornando a integração perfeita. Os modelos correm localmente na sua máquina com total privacidade.

**Comece aqui:** [Ollama](https://ollama.com/)

---

## Comparação de Fornecedores

| Característica | Gemini | DeepSeek | Ollama |
| --- | --- | --- | --- |
| **Requer internet** | Sim | Sim | Não |
| **Requer chave API** | Sim | Sim | Não |
| **Custo** | Plano gratuito disponível | Pago | Gratuito |
| **Privacidade** | Nuvem | Nuvem | Totalmente local |
| **Velocidade** | Rápido | Rápido | Depende do hardware |
| **Ideal para** | Uso diário | Análise aprofundada | Offline/air-gapped |

---

## Personalizando Modelos

Substitua a versão padrão do modelo por fornecedor:

```bash
# ~/.gitpr/.env
GEMINI_API_MODEL=gemini-2.5-pro     # Para análises mais pesadas
DEEPSEEK_API_MODEL=deepseek-reasoner # Para raciocínio complexo
```

---

## Como Funciona

O GitPR usa uma **camada de abstração de fornecedor** que normaliza as chamadas de API independentemente do motor subjacente. A ferramenta:

1. Lê a sua preferência de fornecedor de `~/.gitpr/.env` (ou da flag `--provider`)
2. Encaminha o prompt para o SDK correto (Google GenAI, compatível com OpenAI, ou Ollama local)
3. Faz o parsing da resposta num formato unificado
4. Aplica cache, map-reduce e formatação de saída de forma idêntica para todos os fornecedores

Isto significa que trocar de fornecedor muda **apenas** o motor de IA — todas as outras funcionalidades (linter, cache, i18n, skills) funcionam exatamente da mesma forma.

---

[← Guia do Linter](/linter) &nbsp;|&nbsp; [Sistema de Skills →](/skills)
