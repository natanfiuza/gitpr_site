# Provedores IA — Arquitetura Multi-Modelo

O GitPR é **agnóstico de IA**: não está vinculado a um único modelo ou fornecedor. Durante a configuração, escolha seu motor padrão. Alterne a qualquer momento com uma única flag.

---

## Provedores Suportados

### Google Gemini

O provedor padrão e recomendado. Rápido, econômico e com suporte nativo.

```bash
# Definir como padrão em ~/.gitpr/.env
GITPR_PROVIDER=gemini
GEMINI_API_MODEL_PRIMARY=gemini-pro-latest

# Ou usar uma vez
gitpr --provider gemini
```

**Modelo padrão:** `gemini-pro-latest`

**Obtenha sua chave API:** [Google AI Studio](https://aistudio.google.com/apikey)

---

### DeepSeek

Alternativa poderosa com excelente capacidade de análise de código. Usa o SDK compatível com OpenAI.

```bash
# Definir como padrão em ~/.gitpr/.env
GITPR_PROVIDER=deepseek
DEEPSEEK_API_MODEL_PRIMARY=deepseek-v4-pro

# Ou usar uma vez
gitpr --provider deepseek
```

**Modelo padrão:** `deepseek-v4-pro`

**Obtenha sua chave API:** [DeepSeek Platform](https://platform.deepseek.com/api_keys)

---

### Ollama (Local)

Execute modelos **totalmente offline** — sem internet, sem chaves API, sem cotas.

```bash
# Definir como padrão em ~/.gitpr/.env
GITPR_PROVIDER=ollama

# Ou usar uma vez
gitpr --provider ollama
```

O Ollama usa o formato de API compatível com OpenAI, tornando a integração perfeita. Os modelos rodam localmente na sua máquina com total privacidade.

**Comece aqui:** [Ollama](https://ollama.com/)

---

## Comparação de Provedores

| Característica | Gemini | DeepSeek | Ollama |
| --- | --- | --- | --- |
| **Requer internet** | Sim | Sim | Não |
| **Requer chave API** | Sim | Sim | Não |
| **Custo** | Plano gratuito disponível | Pago | Gratuito |
| **Privacidade** | Nuvem | Nuvem | Totalmente local |
| **Velocidade** | Rápido | Rápido | Depende do hardware |
| **Ideal para** | Uso diário | Análise profunda | Offline/air-gapped |

---

## Personalizando Modelos

Substitua a versão padrão do modelo por provedor:

```bash
# ~/.gitpr/.env
GEMINI_API_MODEL_PRIMARY=gemini-2.5-pro     # Para análises mais pesadas
DEEPSEEK_API_MODEL_PRIMARY=deepseek-reasoner # Para raciocínio complexo
```

---

## Como Funciona

O GitPR usa uma **camada de abstração de provedor** que normaliza as chamadas de API independentemente do motor subjacente. A ferramenta:

1. Lê sua preferência de provedor de `~/.gitpr/.env` (ou da flag `--provider`)
2. Roteia o prompt para o SDK correto (Google GenAI, compatível com OpenAI, ou Ollama local)
3. Faz o parsing da resposta em um formato unificado
4. Aplica cache, map-reduce e formatação de saída de forma idêntica para todos os provedores

Isso significa que trocar de provedor muda **apenas** o motor de IA — todas as outras funcionalidades (linter, cache, i18n, skills) funcionam exatamente da mesma forma.

---

[← Guia do Linter](/linter) &nbsp;|&nbsp; [Sistema de Skills →](/skills)
