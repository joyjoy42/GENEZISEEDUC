# Mettre la plateforme en ligne

Procédure complète pour un VPS Ubuntu 24.04 LTS (Hetzner CX23 ou équivalent).
Compte un peu moins de deux heures la première fois.

Tout ce qui suit se fait avec **tes** comptes : je ne peux pas créer
l'hébergement, acheter le domaine ni générer les certificats à ta place.

---

## 0. Avant tout : mettre le code sur GitHub

Le dépôt local n'a **aucun commit** pour l'instant, et le déploiement repose
sur `git pull`. Il faut donc d'abord :

1. Vérifier que `.gitignore` exclut bien `.env` et `/database/*.sqlite` (c'est fait).
2. Faire le premier commit.
3. Créer un dépôt **privé** sur GitHub et y pousser `master`.

Privé, pas public : le code ne contient pas de secret, mais rien n'oblige à
exposer le back-office et sa structure avant le lancement.

---

## 1. Commander le serveur

- **Hetzner Cloud**, plan d'entrée de gamme (CX23 au moment où j'écris),
  **localisation Allemagne** (Nuremberg ou Falkenstein).
  Depuis Cotonou on mesure 80–140 ms vers l'Allemagne, contre ~187 ms vers
  Johannesburg : l'Europe est le bon choix pour ton public.
- Image : **Ubuntu 24.04 LTS**.
- Ajoute ta **clé SSH** à la création. N'active pas l'accès par mot de passe.

Vérifie le prix du jour : Hetzner a retiré l'ancien CX22 et augmenté ses
tarifs en juin 2026.

## 2. Pointer un sous-domaine

Tant que le nom de la plateforme n'est pas tranché, utilise un sous-domaine
d'un domaine que tu possèdes déjà, par exemple `orientation.tondomaine.tld`.

Crée un enregistrement **A** qui pointe vers l'IPv4 du serveur, et un
enregistrement **AAAA** vers son IPv6. Attends que ça se propage (`dig`).

## 3. Préparer le serveur

Connecte-toi en SSH, puis :

```bash
apt update && apt upgrade -y
apt install -y nginx git unzip curl \
    php8.3-fpm php8.3-cli php8.3-sqlite3 php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-intl php8.3-zip php8.3-bcmath
```

L'application demande **PHP 8.3 minimum** : pas besoin de PHP 8.5 comme sur ta
machine, la version des dépôts Ubuntu convient.

Composer et Node :

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && apt install -y nodejs
```

Un pare-feu minimal :

```bash
ufw allow OpenSSH && ufw allow 'Nginx Full' && ufw enable
```

## 4. Installer l'application

```bash
mkdir -p /var/www && cd /var/www
git clone git@github.com:TON-COMPTE/genezise-orientation.git genezise
cd genezise

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.production.example .env
nano .env          # renseigne APP_URL et le chemin absolu de la base
php artisan key:generate
```

Crée la base et applique les migrations :

```bash
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --class=QuestionnaireSeeder --force
```

Le `QuestionnaireSeeder` installe les 24 questions du test : sans lui, le test
ne peut pas fonctionner. Le `BibliothequeSeeder`, lui, contient du contenu de
démonstration avec des passages entre crochets — **ne le lance pas en
production**, saisis tes vraies fiches depuis le back-office.

Crée ton compte :

```bash
php artisan genezise:administrateur "Joy AKODEKOU" ton-email@exemple.com
```

Le mot de passe généré ne s'affiche qu'une fois. Note-le tout de suite.

## 5. Les permissions

Nginx et PHP tournent sous `www-data`. Il leur faut l'écriture sur trois
endroits, et **le dossier** de la base, pas seulement le fichier — SQLite y
écrit aussi les fichiers `-wal` et `-shm` :

```bash
chown -R www-data:www-data /var/www/genezise/storage \
                           /var/www/genezise/bootstrap/cache \
                           /var/www/genezise/database
chmod -R 775 /var/www/genezise/storage /var/www/genezise/bootstrap/cache
```

## 6. Nginx

`/etc/nginx/sites-available/genezise` :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name orientation.tondomaine.tld;
    root /var/www/genezise/public;

    index index.php;
    charset utf-8;

    client_max_body_size 8M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Rien de sensible ne doit être servi directement.
    location ~ /\.(?!well-known).* { deny all; }

    access_log /var/log/nginx/genezise-access.log;
    error_log  /var/log/nginx/genezise-error.log;
}
```

Puis :

```bash
ln -s /etc/nginx/sites-available/genezise /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
```

> **Important.** L'application fait confiance aux en-têtes `X-Forwarded-*`
> (`trustProxies(at: '*')` dans `bootstrap/app.php`). C'est correct **parce que**
> Nginx est le seul point d'entrée et que PHP-FPM écoute sur une socket Unix.
> Si un jour l'application est exposée directement, cette confiance permettrait
> de falsifier l'adresse IP d'un visiteur — et donc de contourner les limites
> anti-bruteforce. Dans ce cas, remplace `'*'` par l'IP du proxy.

## 7. HTTPS

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d orientation.tondomaine.tld
```

Certbot modifie la configuration Nginx et installe le renouvellement
automatique. Vérifie-le : `systemctl list-timers | grep certbot`.

**Ne saute pas cette étape.** Sans HTTPS, le mot de passe de chaque membre de
l'équipe circule en clair, et `SESSION_SECURE_COOKIE=true` empêchera de toute
façon la connexion de fonctionner.

## 8. Mettre en cache et vérifier

```bash
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Puis, depuis ta machine :

- `https://orientation.tondomaine.tld` — l'accueil s'affiche
- `/test` — le questionnaire se remplit et donne un profil
- `/connexion` — tu peux ouvrir le back-office
- `/up` — répond `200` (c'est la sonde de santé de Laravel)
- une URL inexistante — la page 404 aux couleurs du site, **pas** une trace
  d'erreur Laravel. Si tu vois une trace, `APP_DEBUG` n'est pas à `false`.

## 9. Sauvegarder

Toute la plateforme tient dans un fichier. Une sauvegarde quotidienne hors du
serveur suffit — un serveur qui meurt emporte ses sauvegardes locales.

`crontab -e` :

```cron
0 2 * * * sqlite3 /var/www/genezise/database/database.sqlite ".backup '/var/backups/genezise-$(date +\%F).sqlite'"
```

`.backup` plutôt que `cp` : en WAL, copier le fichier pendant une écriture
donne une sauvegarde corrompue. Installe `sqlite3` (`apt install sqlite3`) et
récupère ces fichiers ailleurs (ton ordinateur, un stockage objet).

Teste une restauration **avant** d'en avoir besoin.

---

## Déployer une mise à jour

Une fois tout en place, chaque nouvelle version se déploie avec :

```bash
cd /var/www/genezise && ./deploy.sh
```

Le script met le site en maintenance, récupère le code, installe les
dépendances, migre, recompile les assets, remet les caches à jour et remet en
ligne — même si une étape échoue.

Rends-le exécutable une fois pour toutes :

```bash
git update-index --chmod=+x deploy.sh
```

---

## Ce qui reste à faire plus tard

- **Content-Security-Policy.** Pas posée : les styles sont en attributs
  `style="…"` et le back-office utilise des `onsubmit` en ligne. Il faudra
  d'abord sortir ce code des pages.
- **Passer à MySQL ou PostgreSQL.** Inutile aujourd'hui : SQLite en mode WAL
  tient très largement le trafic d'un lancement, et se sauvegarde en un
  fichier. Le jour où plusieurs personnes écriront en continu, c'est un
  changement de configuration, pas de code.
- **Supprimer le compte `test@genezise.local`** s'il a été créé sur ce serveur.
