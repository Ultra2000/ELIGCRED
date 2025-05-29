@echo off
echo Installation de EligCred sur Windows...

echo.
echo 1. Verification des pre-requis...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo Composer n'est pas installe. Veuillez l'installer depuis https://getcomposer.org/download/
    pause
    exit /b 1
)

echo.
echo 2. Configuration de l'environnement...
if not exist .env (
    copy .env.example .env
    echo Fichier .env cree.
)

echo.
echo 3. Installation des dependances...
composer install

echo.
echo 4. Generation de la cle d'application...
php artisan key:generate

echo.
echo 5. Configuration de la base de donnees...
echo Veuillez creer une base de donnees 'eligcred' dans phpMyAdmin
echo Puis appuyez sur une touche pour continuer...
pause

echo.
echo 6. Execution des migrations...
php artisan migrate

echo.
echo 7. Configuration du stockage...
php artisan storage:link

echo.
echo Installation terminee !
echo.
echo Pour demarrer le serveur :
echo 1. Lancez XAMPP Control Panel
echo 2. Demarrez Apache et MySQL
echo 3. Accedez a l'application via : http://localhost/eligcred/public
echo.
pause 