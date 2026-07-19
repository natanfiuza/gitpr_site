# Sistema de Skills — Engenharia de Prompt

O GitPR usa **templates Markdown personalizáveis** como instruções de sistema da IA. Em vez de prompts fixos no código, controla a "persona" da IA através de ficheiros locais `.gitpr.*.md` — adaptados às convenções e regras de negócio da sua equipa.

---

## Gerando Templates

```bash
gitpr -s
# ou
gitpr --skill
```

Isto cria os seguintes ficheiros na raiz do seu projeto:

---

## Ficheiros de Template

### `.gitpr.commit.md`
Regras para gerar mensagens de commit. Defina o seu formato preferido, tom e convenções.

```markdown
# Exemplo de personalização
- Use o formato Conventional Commits
- Máximo de 72 caracteres para a linha de resumo
- Inclua escopo quando aplicável: feat(scope): descrição
- Use modo imperativo ("adiciona" e não "adicionado")
```

---

### `.gitpr.pr.md`
Estrutura obrigatória para descrições de Pull Request. Defina secções, nível de detalhe e formatação.

```markdown
# Exemplo de personalização
A sua descrição de PR deve incluir:
1. **Resumo** — um parágrafo descrevendo a alteração
2. **Motivação** — porquê esta alteração é necessária
3. **Testes** — como a alteração foi testada
4. **Screenshots** — se houver alterações de UI
5. **Breaking Changes** — liste quaisquer alterações incompatíveis
```

---

### `.gitpr.review.md`
Foco arquitetural para análise de diff. Defina o que a IA deve priorizar durante os code reviews.

```markdown
# Exemplo de personalização
Foque a sua revisão em:
- Violações dos princípios SOLID
- Vulnerabilidades de segurança (OWASP Top 10)
- Gargalos de desempenho
- Lacunas no tratamento de erros
- Adequação da cobertura de testes
```

---

### `.gitpr.filereview.md`
Regras estritas de coesão e acoplamento para auditoria completa de ficheiros (usado com `--input`).

```markdown
# Exemplo de personalização
Analise este ficheiro em busca de:
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
6. **Critérios de Aceitação** — formato checklist
```

---

### `.gitpr.blame.md`
Foco para análise arqueológica ao rastrear evolução de código legado (usado com `--blame`).

```markdown
# Exemplo de personalização
Ao rastrear histórico de código, identifique:
- Commit e autor original
- Porquê a decisão foi tomada (pelas mensagens de commit)
- Abordagens alternativas consideradas
- Relevância atual das restrições originais
```

---

## Como Funciona

1. **Templates são específicos do projeto** — cada repositório pode ter as suas próprias convenções
2. **IA lê-os como instruções do sistema** — são prefixados a cada prompt relevante
3. **Versionados** — faça commit dos ficheiros para que toda a equipa partilhe os mesmos padrões
4. **Customização sem código** — sem necessidade de modificar o código fonte do GitPR

---

## Exemplo de Fluxo

```bash
# 1. Gerar templates
gitpr -s

# 2. Personalizar para a sua equipa
vim .gitpr.commit.md
vim .gitpr.review.md

# 3. Fazer commit no repositório
git add .gitpr.*.md
git commit -m "feat: adicionar templates de skill personalizados do GitPR"

# 4. Todos os membros da equipa recebem o mesmo comportamento da IA
```

---

[← Fornecedores IA](/providers) &nbsp;|&nbsp; [Internacionalização →](/i18n)
