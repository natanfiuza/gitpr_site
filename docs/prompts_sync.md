# Prompts de sincronização


## 1. Relatório

Utilizando como base o arquivo C:\Users\nataniel\projetos\python\gitpr\docs\reports\relatorio_estado_v0.0.7.md atualize o arquivo public\content\relatorio.md e depois traduza em seus arquivos de outros idiomas 

## 2. Documentação técnica

Com base nos arquivos de C:\Users\nataniel\projetos\python\gitpr\docs atualize minha documentação técnica verificando os arquivos que estão faltando em public\content\docs

## 3. Gerar plano de desenvolviemnto

Melhore esta prompt para um agente de vibe coding, utilize as seguinte regras:

- Não gere nenhum código de exemplo;
- Não de nenhuma sugestão de ruim / bom
- Seja objetivo e direto nas regras de desenvolvimento;
- Não perca tokens desnecessários com coisas obvias

Segue o texto para melhorar:

Quando eu executo o comando para gerar o titulo e a descrição de um pull request o comando padrão "gitpr" e salvo um arquivo segundo a variavel do .env OUTPUT_FILE_NAME, 
se nela não existir um caminho diferente e salvo o arquivo direto na raiz do projeto.
Altere o o local do arquivo de todas as variaveis OUTPUT_FILE_NAME,OUTPUT_FILE_NAME_REVIEW,OUTPUT_FILE_NAME_FULLREVIEW,OUTPUT_FILE_NAME_FILEREVIEW,OUTPUT_FILE_NAME_BLAME,
OUTPUT_FILE_NAME_ISSUE quando não for especificado diretamente uma pasta diferente, salvo os arquivos em ./.gitpr/reports/{pasta_correspondente}.  
Onde {pasta_correspondente} são as mesmas pastas usadas para salvar o cache/prompts exemplo pr_desc,review 

## 4. Executar plano de desenvolvimento

Execute o plano de desenvolvimento:
@docs/plans/20260806_pasta_arquivos_padrao.md  

- Gere o relatorio ao final conforme a regra 

