@echo off
chcp 65001 > nul
color 0A

echo.
echo ================================================
echo   EXPORT DE LA BASE DE DONNEES
echo   E-LLUSION - SAE203
echo ================================================
echo.
echo Demarrage de l'export...
echo.

REM Export de la base de donnees
"D:\Xampp\mysql\bin\mysqldump.exe" -u root --add-drop-table --comments --dump-date --complete-insert --skip-extended-insert --default-character-set=utf8mb4 --set-charset --routines --triggers --events sae203_ellusion > "database.sql" 2>&1

REM Verifier le succes de l'export
if %ERRORLEVEL% EQU 0 goto success
goto error

:success
echo.
echo ================================================
echo   EXPORT REUSSI !
echo ================================================
echo.
echo Le fichier database.sql a ete mis a jour.
echo.

REM Afficher la taille du fichier
for %%A in ("database.sql") do echo Taille du fichier : %%~zA octets
echo.

REM Compter les tables
for /f %%i in ('"D:\Xampp\mysql\bin\mysql.exe" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='sae203_ellusion'" 2^>nul') do set tables=%%i
if defined tables (
    echo Nombre de tables exportees : %tables%
    echo.
)

echo ================================================
echo   PROCHAINES ETAPES
echo ================================================
echo.
echo 1. Verifiez le fichier database.sql
echo 2. Commitez les modifications avec Git :
echo.
echo    cd ..
echo    git add config/database.sql
echo    git commit -m "Mise a jour de la base de donnees"
echo    git push origin main
echo.
echo ================================================
echo.
pause
exit /b 0

:error
echo.
echo ================================================
echo   ERREUR LORS DE L'EXPORT !
echo ================================================
echo.
echo Verifiez que :
echo  - XAMPP est demarre
echo  - MySQL est actif
echo  - La base 'sae203_ellusion' existe
echo.
pause
exit /b 1
