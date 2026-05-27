@echo off
setlocal enabledelayedexpansion

:: Garante que o script rode na pasta onde ele esta (importante para Executar como Admin)
cd /d %~dp0

echo ============================================================
echo   CONFIGURADOR DE AMBIENTE DEEPFACE (SISPONTO)
echo ============================================================
echo.

:: 1. Tentar habilitar Long Paths (requer Admin)
echo [1/3] Habilitando caminhos longos no Windows...
reg add "HKLM\SYSTEM\CurrentControlSet\Control\FileSystem" /v LongPathsEnabled /t REG_DWORD /d 1 /f >nul 2>&1
if %errorlevel% neq 0 (
    echo [AVISO] Nao foi possivel alterar o Registro. 
    echo         Se a instalacao falhar, execute este script como ADMINISTRADOR.
) else (
    echo [OK] Caminhos longos habilitados.
)
echo.

:: 2. Definir caminhos
set VENV_PATH=C:\venv_ponto
set PYTHON_PATH=python

:: Tentar encontrar o Python do Windows Store se o global falhar
where python >nul 2>&1
if %errorlevel% neq 0 (
    set PYTHON_PATH="C:\Users\%USERNAME%\AppData\Local\Microsoft\WindowsApps\python.exe"
)

echo [2/3] Criando ambiente virtual em %VENV_PATH%...
if not exist "%VENV_PATH%" (
    %PYTHON_PATH% -m venv %VENV_PATH%
    if %errorlevel% neq 0 (
        echo [ERRO] Falha ao criar ambiente virtual. Verifique se o Python esta instalado.
        pause
        exit /b 1
    )
    echo [OK] Ambiente virtual criado.
) else (
    echo [INFO] Ambiente virtual ja existe.
)
echo.

:: 3. Instalar dependencias
echo [3/3] Instalando dependencias (isso pode demorar alguns minutos)...
call %VENV_PATH%\Scripts\activate
python -m pip install --upgrade pip
python -m pip install -r requirements.txt

if %errorlevel% neq 0 (
    echo.
    echo [ERRO] Falha na instalacao das dependencias.
    echo        Tente rodar: pip install --no-cache-dir deepface tf-keras
    pause
    exit /b 1
)

echo.
echo ============================================================
echo   CONFIGURACAO CONCLUIDA COM SUCESSO!
echo ============================================================
echo.
echo Agora voce pode fechar esta janela e rodar 'run_deepface.bat'.
echo.
pause
