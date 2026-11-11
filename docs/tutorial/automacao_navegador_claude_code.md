# Automação de navegador no Claude Code

**Projeto:** GitPR Site | **Data:** 11/11/2026 | **Status:** Ativo | **Plataformas:** Windows e macOS/Linux

Tutorial de instalação e uso das três formas de dar "olhos e mãos" de navegador ao
Claude Code: **Playwright MCP**, **Chrome DevTools MCP** e **Playwright como
devDependency**. Com as pegadinhas que apareceram em campo.

---

## 1. O que é e por que usar

O Claude Code, sozinho, não vê a página renderizada: ele lê o código. **MCP** (Model
Context Protocol) é o protocolo que permite plugar ferramentas externas na sessão; um
servidor MCP de navegador entrega ao Claude ferramentas como *navegar*, *clicar*,
*medir elementos* e *tirar screenshot*.

Na prática isso troca "deve funcionar" por "funciona, mede aqui":

- conferir se um layout alinhou de verdade, em vários breakpoints;
- validar tema claro e escuro, menu mobile, estados de hover;
- medir `getBoundingClientRect()` de um elemento e comparar com o esperado;
- reproduzir um bug de front-end sem você precisar descrevê-lo em palavras.

---

## 2. Qual opção escolher

| | Playwright MCP | Chrome DevTools MCP | Playwright (devDependency) |
| --- | --- | --- | --- |
| **O que é** | Servidor MCP que dá 25 ferramentas `browser_*` ao Claude | Servidor MCP do Google que expõe o Chrome DevTools | Biblioteca de automação usada em scripts e testes |
| **Instala** | 1 comando, nada no projeto | 1 comando, nada no projeto | `npm i -D` no projeto |
| **Requisito** | Node 18+ | Node `^20.19` / `^22.12` / `>=23` + Chrome | Node 18+ |
| **Quem dirige** | O Claude, durante a conversa | O Claude, durante a conversa | Seu script/CI |
| **Melhor para** | Verificar uma mudança aqui e agora | Investigar rede, console e performance | Regressão automatizada, CI |

Regra prática: **Playwright MCP** para o dia a dia com o Claude; **DevTools MCP** quando
o problema é de performance, rede ou comportamento do Chrome; **devDependency** quando
o teste precisa rodar sozinho, sem ninguém olhando.

---

## 3. Opção 1 — Playwright MCP (recomendada)

### 3.1 Pré-requisitos

Node.js 18+ e `npm` no PATH. Confira:

```bash
node -v
npm -v
```

### 3.2 Instalação

**Windows** — o `npx` precisa ser invocado via `cmd /c`, senão o servidor não sobe:

```powershell
claude mcp add playwright -- cmd /c npx @playwright/mcp@latest
```

**macOS / Linux:**

```bash
claude mcp add playwright -- npx @playwright/mcp@latest
```

Não é preciso instalar navegador à parte: o servidor usa o Chromium que já vem com o
Playwright (ou o Chrome do sistema, conforme a configuração).

### 3.3 Escopos — onde a configuração fica salva

| Escopo | Flag | Onde vive | Compartilhado? |
| --- | --- | --- | --- |
| Local (padrão) | *(nenhuma)* | `~/.claude.json`, só neste projeto | não |
| Project | `-s project` | `.mcp.json` na raiz do repo | sim — vai no commit |
| User | `-s user` | `~/.claude.json`, global | todos os seus projetos |

Se o mesmo servidor precisa estar disponível em qualquer diretório (e imune à
pegadinha do item 7.2), use escopo de usuário:

```bash
claude mcp add -s user playwright -- npx @playwright/mcp@latest
```

### 3.4 Validar

```bash
claude mcp list
```

Esperado: `playwright: cmd /c npx @playwright/mcp@latest - √ Connected`.

Depois **reinicie a sessão do Claude Code**. Servidores MCP são carregados na
inicialização — instalar no meio da conversa não faz as ferramentas aparecerem nela.
Na sessão nova, `/mcp` lista os servidores conectados.

### 3.5 As 25 ferramentas, por categoria

| Categoria | Ferramentas |
| --- | --- |
| Navegação | `browser_navigate`, `browser_navigate_back`, `browser_tabs`, `browser_close` |
| Inspeção | `browser_snapshot`, `browser_find`, `browser_evaluate`, `browser_console_messages`, `browser_network_requests`, `browser_network_request` |
| Interação | `browser_click`, `browser_type`, `browser_fill_form`, `browser_press_key`, `browser_hover`, `browser_drag`, `browser_drop`, `browser_select_option`, `browser_file_upload`, `browser_handle_dialog`, `browser_wait_for` |
| Visual | `browser_take_screenshot`, `browser_resize`, `browser_emulate_media` |
| Avançado | `browser_run_code_unsafe` (executa Playwright arbitrário no servidor) |

---

## 4. Opção 2 — Chrome DevTools MCP

Servidor mantido pelo time do Chrome. Usa o Chrome já instalado na máquina e expõe
inspeção de rede, console e traces de performance.

```powershell
# Windows
claude mcp add chrome-devtools -- cmd /c npx chrome-devtools-mcp@latest
```

```bash
# macOS / Linux
claude mcp add chrome-devtools -- npx chrome-devtools-mcp@latest
```

Requisito de Node mais estrito que o Playwright: `^20.19.0`, `^22.12.0` ou `>=23`.
Mesma regra de escopo e de reinício de sessão da seção 3.

---

## 5. Opção 3 — Playwright como devDependency

Para scripts e testes que rodam sem o Claude no meio.

```bash
npm i -D @playwright/test
npx playwright install chromium
```

O pacote `@playwright/test` também reexpõe a API dos navegadores, então o mesmo
`chromium` serve para script e para teste.

Exemplo — screenshot em quatro viewports (`package.json` deste projeto usa ESM):

```js
// scripts/screenshots.mjs
import { chromium } from '@playwright/test';

const url = process.argv[2] ?? 'http://127.0.0.1:8000/';
const viewports = [
    { name: 'desktop', width: 1920, height: 1080 },
    { name: 'laptop',  width: 1280, height: 900 },
    { name: 'tablet',  width: 1024, height: 800 },
    { name: 'mobile',  width: 390,  height: 844 },
];

const browser = await chromium.launch();
for (const vp of viewports) {
    const page = await browser.newPage({ viewport: { width: vp.width, height: vp.height } });
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.screenshot({ path: `screenshots/${vp.name}-${vp.width}.png`, fullPage: true });
    await page.close();
}
await browser.close();
```

```bash
node scripts/screenshots.mjs http://127.0.0.1:8000/
```

Adicione `screenshots/` ao `.gitignore` — as imagens não pertencem ao repositório.

---

## 6. Exemplos práticos

### 6.1 Medir a geometria de um elemento

Depois de `browser_navigate`, use `browser_evaluate`:

```js
() => {
  const el = document.querySelector('main > div');
  const b = el.getBoundingClientRect();
  return {
    left: b.left, right: b.right, width: b.width,
    horizontalScroll: document.documentElement.scrollWidth > window.innerWidth
  };
}
```

Foi assim que se provou, neste projeto, que o conteúdo da documentação passou a
começar a 40px da barra do menu lateral (antes eram ~472px).

### 6.2 Screenshot num viewport específico

1. `browser_resize` com `{ width: 1280, height: 900 }`
2. `browser_take_screenshot` com `scale: "css"`

`scale: "css"` gera a imagem no tamanho em pixels CSS — mais leve e comparável entre
máquinas. `"device"` usa o device pixel ratio e gera imagens maiores.

### 6.3 Simular tema escuro

Dois caminhos, e a diferença importa:

- `browser_emulate_media` com `colorScheme: "dark"` — só tem efeito se o site usa
  `prefers-color-scheme` no CSS.
- Sites com **tema por classe** (Tailwind `darkMode: "class"`, como este projeto) ignoram
  a emulação: é preciso clicar no próprio botão de tema, com `browser_click`.

### 6.4 Conferir sobreposição e rolagem horizontal

```js
() => {
  const a = document.querySelector('main > div').getBoundingClientRect();
  const b = document.querySelector('main > aside').getBoundingClientRect();
  return {
    overlap: a.right > b.left,
    gap: b.left - a.right,
    horizontalScroll: document.documentElement.scrollWidth > window.innerWidth
  };
}
```

---

## 7. Solução de problemas

| Sintoma | Causa | Correção |
| --- | --- | --- |
| `spawn npx ENOENT` / servidor não conecta no Windows | `npx` não é executável direto pelo spawn | usar `cmd /c npx ...` (seção 3.2) |
| `claude mcp list` não lista o servidor, mas ele está configurado | letra do drive diverge: gravado como `C:` e lido como `c:` quando o comando roda no Git Bash | rodar o comando no PowerShell, ou registrar com `-s user` |
| Instalou mas as ferramentas não aparecem | MCP carrega só na inicialização da sessão | reiniciar o Claude Code e conferir com `/mcp` |
| `.playwright-mcp/` aparece no `git status` | o servidor grava snapshots e screenshots ali | adicionar `/.playwright-mcp/` ao `.gitignore` |
| Site local abre em branco depois de matar o Vite | `public/hot` obsoleto aponta para o dev server morto | rodar `npm run dev`, ou apagar `public/hot` para servir o build |

---

## 8. Referência rápida

```bash
# Playwright MCP — Windows
claude mcp add playwright -- cmd /c npx @playwright/mcp@latest
# Playwright MCP — macOS/Linux
claude mcp add playwright -- npx @playwright/mcp@latest
# Para todos os projetos
claude mcp add -s user playwright -- npx @playwright/mcp@latest

# Chrome DevTools MCP
claude mcp add chrome-devtools -- npx chrome-devtools-mcp@latest

# Playwright no projeto
npm i -D @playwright/test && npx playwright install chromium

# Sempre depois de instalar
claude mcp list     # deve mostrar "√ Connected"
# e reiniciar a sessão do Claude Code
```

```gitignore
/.playwright-mcp/
```

---

## 9. Notas de campo

Tudo abaixo foi executado numa máquina Windows 11 com Node 22:

| Item | Verificação |
| --- | --- |
| `claude mcp add ... -- cmd /c npx ...` | servidor conectado, 25 ferramentas expostas |
| `claude mcp list` no PowerShell | lista e mostra `√ Connected` |
| `claude mcp list` no Git Bash | **não lista** o servidor (pegadinha 7.2) — o servidor funciona normalmente |
| Navegação real | abre o Chromium e devolve snapshot da página |
| `@playwright/mcp` | v0.0.85 |
| `chrome-devtools-mcp` | v1.12.0 |

Os comandos de macOS/Linux são as variantes sem `cmd /c`; a parte `npx` é idêntica.
