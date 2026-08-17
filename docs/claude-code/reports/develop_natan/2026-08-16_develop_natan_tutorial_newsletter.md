# Relatório: Tutorial da Newsletter

**Data:** 2026-08-16
**Branch:** develop_natan

## Objetivo

Criar um tutorial de como criar uma newsletter e como o processo funciona, documentando o sistema implementado na tarefa anterior (`2026-08-16_develop_natan_sistema_newsletter.md`).

## O que foi feito

- Criado `docs/tutorial_newsletter.md` com:
  1. **Fluxo do visitante** (inscrição → confirmação 24h → cancelamento one-click → reativação) em diagrama de texto.
  2. **Fluxo de envio** (dicas → corpo → command → e-mail com headers RFC 8058 → marker anti-reenvio).
  3. **Passo a passo** em 7 passos: atualizar relatório (skill `update-relatorio`), atualizar dicas (`update-tip-tools`), gerar corpo (`generate-newsletter-body`), revisar os 5 arquivos, configurar `.env` (MAIL_MAILER/MAIL_FROM_ADDRESS/APP_URL), enviar com `newsletter:send` (regras de `--force`, alerta de volume, isolamento de falha) e verificar.
  4. **Tabela de referência rápida** de arquivos/rotas/tabelas.
  5. **Regras de ouro**: versão da newsletter = versão do relatório (não a do CLI), expiração de 24h com reuso de uuid, um `is_confirmed=true` por e-mail, reativação pós-cancelamento, cancelamento one-click.

## Verificação

- Conteúdo conferido contra a implementação real (rotas, arquivos, regras do command e do controller) — sem divergências.
