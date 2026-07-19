@echo off
:: =====================================================================
:: ROTEADOR DO SCRIPT (Garante que abra em uma nova janela CMD)
:: =====================================================================
if "%~1"=="START_PROCESS" goto :start_process

:: Inicia o processo em uma nova janela isolada do CMD
start "SIG-Novo" cmd /k call "%~f0" START_PROCESS
exit

:: =====================================================================
:: BLOCO PRINCIPAL (Roda dentro da nova janela)
:: =====================================================================
:start_process
title SIG-Novo (Artisan + NPM)
color 0B

:: ASCII Art usando apenas caracteres nativos para evitar erro de codepage no CMD
echo.
echo   ,----.   ,--.  ,--.  ,------. ,------.
echo  '  .-./   `--',-'  '-.^|  .--. '^|  .--. '
echo  ^|  ^| .---.,--.'-.  .-'^|  '--' ^|^|  '--'.'
echo  '  '--'  ^|^|  ^|  ^|  ^|  ^|  ^|  ^| --' ^|  ^|\  \
echo   `------' `--'  `--'  `--'     `--' '--'
echo.
echo  [/// INICIALIZANDO ///] ARTISAN SERVE + NPM RUN DEV
echo  =============================================================
echo.

:: Garante que o script vai rodar na pasta correta (SIG-Novo)
cd /d "%~dp0"

:: Executa ambos no mesmo terminal usando o 'concurrently' via npx
npx -y concurrently -c "blue,magenta" -n "VITE,PHP" "npm run dev" "php artisan serve"
