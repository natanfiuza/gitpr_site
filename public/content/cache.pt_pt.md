# Sistema de Cache e Auto-Updater

O GitPR inclui cache inteligente para poupar quotas de API e um auto-updater perfeito para o manter na versão mais recente.

---

## ⚡ Sistema de Cache Local

Sempre que executa um comando com IA (`--review`, `--commit`, etc.), o GitPR gera um **hash MD5** do seu código atual (diff) combinado com as suas instruções.

Se executar o **mesmo comando** sem alterar o código, o GitPR interceta o pedido e devolve o resultado **instantaneamente da cache** — sem chamada de API, sem gasto de quota.

### Como Funciona

1. Comando executa → diff + instruções são hasheados (MD5)
2. Se o hash existe em `~/.gitpr/cache/prompts/` → devolve resultado em cache
3. Se não → chama a IA, guarda a resposta, devolve o resultado

### Benefícios

- **Zero chamadas duplicadas à API** — re-executar a mesma revisão não custa nada
- **Respostas em milissegundos** — leituras de cache são instantâneas
- **Invalidação automática** — qualquer alteração no código produz um hash diferente
- **Transparente** — sem necessidade de flags, sempre ativo

---

## 🔄 Auto-Updater (Atualização OTA)

O GitPR verifica atualizações silenciosamente em cada execução e pode fazer hot-swap do binário em segundos.

### Verificar e Atualizar

```bash
# Forçar verificação de atualização
gitpr -u
# ou
gitpr --update
```

### Como Funciona

1. **Guardião de Conexão:** Verifica disponibilidade de rede antes de iniciar — nunca bloqueia fluxos de trabalho offline
2. **Verificação silenciosa em background:** Em cada execução, compara a versão local com a última Release do GitHub
3. **Técnica Hot-Swap:** Transfere o novo binário, renomeia o antigo como backup e substitui-o transparentemente — tudo enquanto a execução atual termina normalmente
4. **Capacidade de rollback:** Se a nova versão falhar, o binário antigo ainda está em disco

### Verificação de Versão

O GitPR usa **checksums SHA-256** publicados com cada Release do GitHub para verificar a integridade do binário antes da instalação.

---

## Fluxo Combinado

```bash
# 1. Trabalhe normalmente — a cache evita chamadas duplicadas à API
gitpr -r
gitpr -r  # Mesmo diff → cache hit instantâneo ⚡

# 2. Altere algum código → novo hash → nova chamada à IA
# ... editar ficheiros ...
gitpr -r  # Diff diferente → nova análise

# 3. Mantenha-se atualizado sem esforço
gitpr -u  # Verificar e instalar versão mais recente
```

---

## Armazenamento da Cache

Todos os ficheiros de cache ficam em `~/.gitpr/cache/prompts/`. Pode eliminar este diretório com segurança para libertar espaço em disco — o GitPR recriá-lo-á conforme necessário.

```bash
# Limpar todas as respostas em cache
rm -rf ~/.gitpr/cache/prompts/
```

---

[← Internacionalização](/i18n) &nbsp;|&nbsp; [Contribuição →](/contribuicao)
