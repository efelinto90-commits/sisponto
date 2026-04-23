@echo off
echo ==========================================================
echo Buscando Servicos Locais de Biometria (Portas Abertas)
echo ==========================================================
echo.
echo As portas comuns sao:
echo - BioKay / ZKTeco: 8098, 8080
echo - DigitalPersona: 15270, 52181, 9001
echo - Outros Servicos Locais HTTP/WS: 8000 a 9999
echo.

echo Listando todas as portas LISTENING na sua maquina...
echo (Isso pode demorar alguns segundos)
echo.

netstat -abno | findstr "LISTENING"

echo.
echo ==========================================================
echo FIM DA BUSCA.
echo Observe as linhas acima e procure por programas como 
echo "DPWebClient.exe", "ZKTeco", ou servicos rodando nas
echo portas mencionadas acima (ex: 0.0.0.0:15270).
echo ==========================================================
pause
