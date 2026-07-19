# Sistema de Skills — Engenharia de Prompt

O GitPR usa **templates Markdown personalizáveis** como instruções de sistema da IA. Em vez de prompts fixos no código, você controla a "persona" da IA através de arquivos locais `.gitpr.*.md` — adaptados às convenções e regras de negócio do seu time.

---

## Gerando Templates

```bash
gitpr -s
# ou
gitpr --skill
```

Isso cria os seguintes arquivos na raiz do seu projeto:

---

## Arquivos de Template

### `.gitpr.commit.md`
Regras para gerar mensagens de commit. Defina seu formato preferido, tom e convenções.

```markdown
# Exemplo de personalização
- Use o formato Conventional Commits
- Máximo de 72 caracteres para a linha de resumo
- Inclua escopo quando aplicável: feat(scope): descrição
- Use modo imperativo ("adiciona" não "adicionado")
```

---

### `.gitpr.pr.md`
Estrutura obrigatória para descrições de Pull Request. Defina seções, nível de detalhe e formatação.

```markdown
# Exemplo de personalização
Sua descrição de PR deve incluir:
1. **Resumo** — um parágrafo descrevendo a alteração
2. **Motivação** — por que esta alteração é necessária
3. **Testes** — como a alteração foi testada
4. **Screenshots** — se houver alterações de UI
5. **Breaking Changes** — liste quaisquer alterações incompatíveis
```

---

### `.gitpr.review.md`
Foco arquitetural para análise de diff. Defina o que a IA deve priorizar durante os code reviews.

```markdown
# Exemplo de personalização
Foque sua revisão em:
- Violações dos princípios SOLID
- Vulnerabilidades de segurança (OWASP Top 10)
- Gargalos de performance
- Lacunas no tratamento de erros
- Adequação da cobertura de testes
```

---

### `.gitpr.filereview.md`
Regras estritas de coesão e acoplamento para auditoria completa de arquivos (usado com `--input`).

```markdown
# Exemplo de personalização
Analise este arquivo em busca de:
- Violações do Princípio da Responsabilidade Única
- Acoplamento forte com serviços externos
- Falta de injeção de dependência
- Números mágicos e valores hardcoded
- Funções com mais de 30 linhas
```

---

### `.gitpr.issue.md`
Estrutura e nível de detalhe para geração padronizada de issues (usado com `--issue`).

```markdown
# Exemplo de personalização
As issues devem conter:
1. **Descrição** — declaração clara do problema
2. **Passos para Reproduzir** — lista numerada
3. **Comportamento Esperado**
4. **Comportamento Atual**
5. **Ambiente** — SO, versão, etc.
6. **Critérios de Aceite** — formato checklist
```

---

### `.gitpr.blame.md`
Foco para análise arqueológica ao rastrear evolução de código legado (usado com `--blame`).

```markdown
# Exemplo de personalização
Ao rastrear histórico de código, identifique:
- Commit e autor original
- Por que a decisão foi tomada (pelas mensagens de commit)
- Abordagens alternativas consideradas
- Relevância atual das restrições originais
```

---

## Como Funciona

1. **Templates são específicos do projeto** — cada repositório pode ter suas próprias convenções
2. **IA os lê como instruções do sistema** — são prefixados a cada prompt relevante
3. **Versionados** — commit nos arquivos para que todo o time compartilhe os mesmos padrões
4. **Customização sem código** — sem necessidade de modificar o código fonte do GitPR

---

## Exemplo de Fluxo

```bash
# 1. Gerar templates
gitpr -s

# 2. Personalizar para seu time
vim .gitpr.commit.md
vim .gitpr.review.md

# 3. Commitar no repositório
git add .gitpr.*.md
git commit -m "feat: adicionar templates de skill personalizados do GitPR"

# 4. Todo membro do time agora recebe o mesmo comportamento da IA
```

---

[← Provedores IA](/providers) &nbsp;|&nbsp; [Internacionalização →](/i18n)
