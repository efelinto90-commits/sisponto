@echo off
echo ============================================================
echo   Iniciando Servico DeepFace (Sisponto)
echo ============================================================
echo.

cd /d %~dp0

set VENV_PATH=C:\venv_ponto

if not exist "%VENV_PATH%\Scripts\activate" (
    echo [ERRO] Ambiente virtual nao encontrado em %VENV_PATH%
    echo        Execute 'setup_venv.bat' primeiro!
    pause
    exit /b 1
)

echo [INFO] Ativando ambiente virtual...
call "%VENV_PATH%\Scripts\activate"

echo [INFO] Verificando dependencias...
:: Ocultamos a saida se estiver tudo OK para nao poluir o terminal
python -c "import deepface" >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Instalando/Atualizando dependencias...
    python -m pip install -r requirements.txt
)

echo [OK] Iniciando deepface_service.py...
python deepface_service.py

pause
