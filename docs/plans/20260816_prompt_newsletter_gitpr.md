# Feature: Sistema de Newsletter — GitPR Site

## Contexto
Nova feature no site do GitPR: sistema de newsletter (cadastro, confirmação, envio e cancelamento).

## Stack
Laravel 13 + Inertia + Vue.

## Interface — Formulário de Cadastro
Adicionar na coluna da direita do site um box para o visitante informar o e-mail e se cadastrar para receber novidades sobre novas versões.

## Estrutura de Dados
Criar migrations e models com tabelas prefixadas com `newsletter_`.

Model `NewsletterSubscriber` → tabela `newsletter_subscribers`, com os campos:
- nome (obrigatório)
- email (obrigatório, readonly na tela de confirmação)
- identificador do GitHub (opcional, ex: owner do repositório)
- telefone (opcional)
- lang (obrigatório; se o parâmetro `?lang=<idioma>` não estiver presente no link de confirmação, usar inglês como padrão)
- is_canceled (padrão true; indica cancelamento da inscrição)
- date_canceled (data/hora do cancelamento)

Model `NewsLetterConfirmation` → tabela `newsletter_confirmations`, com os campos:
- uuid
- email
- is_confirmed
- date_confirmed

Regras da tabela `newsletter_confirmations`:
- O registro expira se não for confirmado em 24 horas, com base em `created_at`.
- O mesmo e-mail pode ter múltiplos registros com `is_confirmed = false`. Se expirou as 24 horas.
- O mesmo e-mail não pode ter mais de um registro com `is_confirmed = true`.

## Fluxo de Confirmação
Ao inserir o e-mail, o usuário deve receber um e-mail de confirmação.

O link de confirmação deve ser: `GET /newsletter/confirm/{uuid}?lang=<idioma>`.

O `{uuid}` corresponde ao campo `uuid` da tabela `newsletter_confirmations`. Se ele informar novamente antes de confirmar envia o mesmo `uuid` do registro não espirado.

Se o usuário informar um e-mail já confirmado, `is_confirmed = true` deve ser informado, com opção de envio de um link de cancelamento, onde vai apenas a mensagem no idioma correto e o link para que o proprietario do e-mail confirme.

Ao acessar o link, abrir uma tela de confirmação com:
- Barra superior contendo "GitPR [cli]" no canto esquerdo.
- No canto direito: versão, ícone de alternância de tema (claro/escuro) e seletor de idioma.
- Suporte aos idiomas: en, pt_br, pt_pt, fr, es.
- Formulário com os campos do model `NewsletterSubscriber` (nome, email, identificador do GitHub, telefone, lang).
- Texto informando que, após a confirmação, o usuário pode cancelar a assinatura a qualquer momento.

Os dados preenchidos no formulário da tela de confirmação devem ser persistidos em `newsletter_subscribers`.

## E-mails
Utilizar Mailable do Laravel e Blade para o corpo de todos os e-mails (confirmação e newsletter).

O corpo do e-mail da newsletter deve ser composto a partir de um Markdown salvo em `public/content/newsletter/{version}/newsletter_body.md`. Este arquivo não deve ser incluído no `menu.json`.

O corpo do e-mail da newsletter deve incluir:
- Cabeçalhos compatíveis com detecção automática de cancelamento por clientes de e-mail (Gmail, Outlook).
- Link de cancelamento ao final do e-mail.

## Tela de Cancelamento
Deve exibir texto i18n de acordo com o idioma do subscriber e um botão de confirmação de cancelamento.

## Versionamento de Conteúdo
Criar `public/content/newsletter/{version}/newsletter_body.md` com conteúdo derivado de `public/content/relatorio.md`, adaptado para conter apenas: novidades da versão, como usar, e dicas úteis gerais.

A versão da newsletter deve corresponder à versão do relatório, evitando reenvio de conteúdo já divulgado.

## Dicas Úteis (Tips)
Criar `public/content/tip_tools.json` com dicas úteis baseadas na documentação técnica, para uso nas newsletters.

Cada dica deve conter um campo indicando se já foi utilizada, evitando repetição entre newsletters.

Para gerar as dicas uteis procure em `public/content/**/*.md`  utilizando os seguintes critérios:

- Dicas de uso
- As features existentes,  exemplo o chat, mcp etc..
- Curiosidades do gitpr
- Seja criativo

### Automação de Dicas Úteis

Crie uma skill para atualizar as dicas uteis, ela deve varrer todos os arquivos .md  em `public/content/**/*.md`  e procurar por: 

- Dicas de uso
- As features existentes,  exemplo o chat, mcp etc..
- Curiosidades do gitpr
- Seja criativo   

## Automação
Criar uma skill local para gerar `public/content/newsletter/{version}/newsletter_body.md` a partir de `public/content/relatorio.md`.

Criar um Artisan Command para execução manual que envia os e-mails com intervalo de 5 segundos entre cada envio. Esta é uma solução temporária.

O command deve emitir um alerta e interromper a execução quando o volume de e-mails cadastrados for grande o suficiente para tornar o envio demorado (múltiplas horas).

## Dúvidas
Caso falte alguma informação necessária para a implementação, ou existam pontos ambíguos, perguntar antes de prosseguir.
