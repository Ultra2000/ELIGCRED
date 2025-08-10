# Guide de Déploiement - Système d'Évaluation de Crédit

## 📋 Prérequis de Production

### Serveur Web
- **OS** : Ubuntu 20.04+ ou CentOS 8+
- **Serveur Web** : Nginx ou Apache
- **PHP** : 8.2 ou supérieur avec extensions :
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - ZIP

### Base de Données
- **MySQL** : 8.0+ ou **PostgreSQL** : 13+
- **SQLite** : Pour les environnements de test

### Python et IA
- **Python** : 3.8+
- **Packages** : scikit-learn, joblib, numpy, pandas

### Autres
- **Composer** : 2.0+
- **Node.js** : 18+ avec npm
- **Supervisor** : Pour la gestion des queues

## 🚀 Déploiement Étape par Étape

### 1. Préparation du Serveur

#### Installation des Dépendances (Ubuntu)

```bash
# Mise à jour du système
sudo apt update && sudo apt upgrade -y

# Installation de PHP et extensions
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd -y

# Installation de Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation de Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Installation de Python et pip
sudo apt install python3 python3-pip -y
pip3 install scikit-learn joblib numpy pandas
```

#### Configuration Nginx

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/credit-evaluation/public;
    index index.php;

    # Sécurité
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    # Gestion des fichiers statiques
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Configuration PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Sécurité - Bloquer l'accès aux fichiers sensibles
    location ~ /\. {
        deny all;
    }

    # Optimisation des fichiers statiques
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 2. Déploiement de l'Application

#### Clonage et Installation

```bash
# Création du répertoire
sudo mkdir -p /var/www/credit-evaluation
sudo chown $USER:$USER /var/www/credit-evaluation

# Clonage du projet
cd /var/www/credit-evaluation
git clone <repository-url> .

# Installation des dépendances PHP
composer install --optimize-autoloader --no-dev

# Installation des dépendances Node.js
npm ci --only=production
npm run build
```

#### Configuration de l'Environnement

```bash
# Copie du fichier d'environnement
cp .env.example .env

# Génération de la clé d'application
php artisan key:generate
```

#### Configuration du fichier `.env` pour la production

```env
APP_NAME="Système d'Évaluation de Crédit"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_GENEREE
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=credit_evaluation
DB_USERNAME=credit_user
DB_PASSWORD=mot_de_passe_securise

# Cache et Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (optionnel)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

### 3. Configuration de la Base de Données

#### Création de la Base de Données MySQL

```sql
CREATE DATABASE credit_evaluation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'credit_user'@'localhost' IDENTIFIED BY 'mot_de_passe_securise';
GRANT ALL PRIVILEGES ON credit_evaluation.* TO 'credit_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Migration et Seeders

```bash
# Exécution des migrations
php artisan migrate --force

# Création de l'utilisateur admin (optionnel)
php artisan make:filament-user
```

### 4. Configuration des Permissions

```bash
# Propriétaire des fichiers
sudo chown -R www-data:www-data /var/www/credit-evaluation

# Permissions des dossiers
sudo chmod -R 755 /var/www/credit-evaluation
sudo chmod -R 775 /var/www/credit-evaluation/storage
sudo chmod -R 775 /var/www/credit-evaluation/bootstrap/cache

# Permissions spéciales pour les modèles IA
sudo chmod -R 755 /var/www/credit-evaluation/storage/app/models
```

### 5. Optimisation pour la Production

#### Cache Laravel

```bash
# Cache de configuration
php artisan config:cache

# Cache des routes
php artisan route:cache

# Cache des vues
php artisan view:cache

# Cache des événements
php artisan event:cache
```

#### Configuration Supervisor pour les Queues

Créer `/etc/supervisor/conf.d/credit-evaluation-worker.conf` :

```ini
[program:credit-evaluation-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/credit-evaluation/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/credit-evaluation/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Recharger Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start credit-evaluation-worker:*
```

### 6. Configuration SSL (Let's Encrypt)

```bash
# Installation de Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtention du certificat SSL
sudo certbot --nginx -d votre-domaine.com

# Renouvellement automatique
sudo crontab -e
# Ajouter : 0 12 * * * /usr/bin/certbot renew --quiet
```

### 7. Monitoring et Maintenance

#### Logs et Monitoring

```bash
# Rotation des logs Laravel
sudo nano /etc/logrotate.d/credit-evaluation
```

Contenu du fichier :
```
/var/www/credit-evaluation/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

#### Scripts de Maintenance

Créer `/var/www/credit-evaluation/scripts/maintenance.sh` :

```bash
#!/bin/bash

# Nettoyage des logs anciens
php artisan telescope:prune --hours=48

# Nettoyage du cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimisation de la base de données
php artisan model:prune

echo "Maintenance terminée - $(date)"
```

#### Tâche Cron pour la Maintenance

```bash
sudo crontab -e
# Ajouter :
0 2 * * * cd /var/www/credit-evaluation && /bin/bash scripts/maintenance.sh >> storage/logs/maintenance.log 2>&1
```

### 8. Sauvegarde

#### Script de Sauvegarde

Créer `/var/www/credit-evaluation/scripts/backup.sh` :

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/credit-evaluation"
DATE=$(date +%Y%m%d_%H%M%S)

# Création du répertoire de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarde de la base de données
mysqldump -u credit_user -p credit_evaluation > $BACKUP_DIR/db_$DATE.sql

# Sauvegarde des fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/credit-evaluation/storage/app

# Nettoyage des anciennes sauvegardes (garder 30 jours)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Sauvegarde terminée - $DATE"
```

#### Programmation des Sauvegardes

```bash
sudo crontab -e
# Ajouter :
0 3 * * * /bin/bash /var/www/credit-evaluation/scripts/backup.sh >> /var/log/credit-evaluation-backup.log 2>&1
```

## 🔧 Configuration Avancée

### Optimisation PHP-FPM

Éditer `/etc/php/8.2/fpm/pool.d/www.conf` :

```ini
; Augmenter les limites pour les gros fichiers Excel
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

; Limites mémoire
php_admin_value[memory_limit] = 512M
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
```

### Configuration Redis (Cache)

```bash
# Installation de Redis
sudo apt install redis-server -y

# Configuration sécurisée
sudo nano /etc/redis/redis.conf
# Décommenter : requirepass votre_mot_de_passe_redis
```

### Monitoring avec Horizon (Optionnel)

Si vous utilisez Laravel Horizon pour les queues :

```bash
composer require laravel/horizon
php artisan horizon:install
php artisan horizon:publish
```

## 🚨 Sécurité en Production

### Firewall

```bash
# Configuration UFW
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Sécurisation des Fichiers

```bash
# Masquer les informations sensibles
sudo nano /var/www/credit-evaluation/.htaccess
```

```apache
# Bloquer l'accès aux fichiers sensibles
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>
```

### Mise à Jour de Sécurité

```bash
# Script de mise à jour automatique
sudo nano /etc/cron.daily/security-updates
```

```bash
#!/bin/bash
apt update && apt upgrade -y
```

## 📊 Surveillance et Alertes

### Vérification de Santé

Créer un script de monitoring `/var/www/credit-evaluation/scripts/health-check.sh` :

```bash
#!/bin/bash

# Vérifier que l'application répond
if curl -f http://localhost/up > /dev/null 2>&1; then
    echo "OK - Application accessible"
else
    echo "ERREUR - Application inaccessible"
    exit 1
fi

# Vérifier les queues
QUEUE_SIZE=$(php artisan queue:size)
if [ $QUEUE_SIZE -gt 100 ]; then
    echo "ATTENTION - Queue surchargée: $QUEUE_SIZE jobs"
fi

# Vérifier l'espace disque
DISK_USAGE=$(df /var/www/credit-evaluation | tail -1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "ATTENTION - Espace disque faible: $DISK_USAGE%"
fi
```

### Logs Centralisés

Configuration pour envoyer les logs vers un service externe (optionnel) :

```env
# Dans .env
LOG_CHANNEL=stack
LOG_STACK=single,syslog

# Pour Papertrail ou similaire
SYSLOG_HOST=logs.papertrailapp.com
SYSLOG_PORT=12345
```

## 🔄 Processus de Mise à Jour

### Script de Déploiement

Créer `/var/www/credit-evaluation/scripts/deploy.sh` :

```bash
#!/bin/bash

set -e

echo "🚀 Début du déploiement..."

# Sauvegarde avant mise à jour
./scripts/backup.sh

# Mise en mode maintenance
php artisan down

# Mise à jour du code
git pull origin main

# Mise à jour des dépendances
composer install --optimize-autoloader --no-dev
npm ci --only=production
npm run build

# Mise à jour de la base de données
php artisan migrate --force

# Nettoyage et optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Redémarrage des services
sudo supervisorctl restart credit-evaluation-worker:*
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

# Sortie du mode maintenance
php artisan up

echo "✅ Déploiement terminé avec succès!"
```

### Rollback

Créer `/var/www/credit-evaluation/scripts/rollback.sh` :

```bash
#!/bin/bash

set -e

echo "🔄 Début du rollback..."

# Mode maintenance
php artisan down

# Retour au commit précédent
git reset --hard HEAD~1

# Restauration des dépendances
composer install --optimize-autoloader --no-dev

# Rollback de la base de données si nécessaire
# php artisan migrate:rollback --force

# Nettoyage du cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Redémarrage des services
sudo supervisorctl restart credit-evaluation-worker:*

# Sortie du mode maintenance
php artisan up

echo "✅ Rollback terminé!"
```

## 📈 Performance et Optimisation

### Configuration PHP pour Production

Éditer `/etc/php/8.2/fpm/php.ini` :

```ini
; Optimisations générales
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1

; Limites pour les fichiers Excel
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 512M
max_execution_time = 300
```

### Optimisation Base de Données

```sql
-- Index pour améliorer les performances
CREATE INDEX idx_dossiers_statut ON dossiers(statut);
CREATE INDEX idx_dossiers_created_at ON dossiers(created_at);
CREATE INDEX idx_avis_dossier_id ON avis(dossier_id);
CREATE INDEX idx_evaluations_ia_dossier_id ON evaluations_ia(dossier_id);
```

### Cache Redis

Configuration pour améliorer les performances :

```env
# Dans .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=votre_mot_de_passe_redis
REDIS_PORT=6379
```

## 🔐 Sécurité Avancée

### Configuration SSL/TLS

```nginx
# Configuration Nginx avec SSL
server {
    listen 443 ssl http2;
    server_name votre-domaine.com;
    
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    
    # Configuration SSL moderne
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=63072000" always;
    
    # ... reste de la configuration
}

# Redirection HTTP vers HTTPS
server {
    listen 80;
    server_name votre-domaine.com;
    return 301 https://$server_name$request_uri;
}
```

### Protection contre les Attaques

```env
# Dans .env - Configuration de sécurité
APP_DEBUG=false
APP_ENV=production

# Rate limiting plus strict
THROTTLE_REQUESTS=30
THROTTLE_DECAY_MINUTES=1
```

## 📋 Checklist de Déploiement

### Avant le Déploiement

- [ ] Tests unitaires passent
- [ ] Tests d'intégration passent
- [ ] Sauvegarde de la base de données actuelle
- [ ] Vérification des variables d'environnement
- [ ] Test des modèles IA en local

### Pendant le Déploiement

- [ ] Mode maintenance activé
- [ ] Code mis à jour
- [ ] Dépendances installées
- [ ] Migrations exécutées
- [ ] Cache optimisé
- [ ] Services redémarrés

### Après le Déploiement

- [ ] Application accessible
- [ ] Endpoint `/up` répond
- [ ] Interface admin fonctionnelle
- [ ] API d'évaluation testée
- [ ] Logs vérifiés
- [ ] Performance surveillée

## 🆘 Dépannage

### Problèmes Courants

#### Application Inaccessible
```bash
# Vérifier les logs
tail -f /var/log/nginx/error.log
tail -f storage/logs/laravel.log

# Vérifier les services
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
```

#### Erreurs de Permissions
```bash
# Corriger les permissions
sudo chown -R www-data:www-data storage/ bootstrap/cache/
sudo chmod -R 775 storage/ bootstrap/cache/
```

#### Problèmes de Queue
```bash
# Redémarrer les workers
sudo supervisorctl restart credit-evaluation-worker:*

# Vérifier le statut
php artisan queue:work --once
```

#### Erreurs Python/IA
```bash
# Tester le script Python
python3 app/Services/test_models.py

# Vérifier les modèles
ls -la storage/app/models/
```

### Contacts d'Urgence

En cas de problème critique :
1. Vérifier les logs d'application
2. Consulter les métriques serveur
3. Exécuter le script de rollback si nécessaire
4. Contacter l'équipe de développement

## 📊 Métriques de Performance

### Indicateurs à Surveiller

- **Temps de réponse API** : < 2 secondes
- **Temps d'évaluation IA** : < 10 secondes
- **Utilisation CPU** : < 70%
- **Utilisation mémoire** : < 80%
- **Espace disque** : < 80%
- **Taux d'erreur** : < 1%

### Outils de Monitoring Recommandés

- **Application** : Laravel Telescope (dev), Bugsnag (prod)
- **Serveur** : htop, iotop, New Relic, DataDog
- **Base de données** : MySQL Workbench, phpMyAdmin
- **Logs** : ELK Stack, Papertrail