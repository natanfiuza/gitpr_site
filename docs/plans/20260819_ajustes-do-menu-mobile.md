# Ajuste de Layout Responsivo — Menu Mobile

## Contexto

Em resoluções com largura menor ou igual a 425px, o site oculta o menu lateral esquerdo e exibe um ícone de sanduíche no canto superior direito. Atualmente esse ícone está posicionado incorretamente em uma barra vertical à esquerda.

## Comportamento Esperado

Ao atingir largura menor ou igual a 425px:

1. A barra superior deve exibir apenas:
   - O nome "GitPR [CLI]" no canto esquerdo.
   - O ícone de menu sanduíche no canto superior direito.

2. Dentro do menu lateral esquerdo (acionado pelo sanduíche):
   - O seletor de idioma deve ficar fixo no topo do menu.
   - O ícone do GitHub e o botão de alternância de tema (claro/escuro) devem ficar agrupados em uma barra inferior, fixada na parte de baixo do menu.

## Escopo

Aplicar o comportamento apenas na faixa de largura menor ou igual a 425px, preservando o layout atual para resoluções maiores. Garantir que a reorganização dos elementos (seletor de idioma no topo, GitHub e tema na base) não afete a funcionalidade existente de cada componente, apenas seu posicionamento.
