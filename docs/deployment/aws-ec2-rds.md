# Deploiement AWS

## Architecture cible

- Application Laravel deployee sur une instance AWS EC2
- Base de donnees MySQL ou PostgreSQL hebergee sur AWS RDS
- Variables sensibles stockees dans `.env` ou AWS Systems Manager Parameter Store

## Etapes recommandees

1. Creer une instance EC2 avec PHP 8.2+ et un serveur web (`Nginx` ou `Apache`).
2. Creer une base AWS RDS et autoriser uniquement le security group de l'instance EC2.
3. Copier le projet sur EC2 puis executer:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan l5-swagger:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Configurer les variables `APP_URL`, `DB_*`, `SANCTUM_STATEFUL_DOMAINS` si necessaire et `GEMINI_API_KEY`.
5. Configurer HTTPS via un reverse proxy ou un load balancer AWS.

## Points d attention

- Stocker la cle Gemini cote serveur uniquement
- Restreindre l acces RDS
- Activer des sauvegardes automatiques sur RDS
- Superviser les logs Laravel et les erreurs HTTP
