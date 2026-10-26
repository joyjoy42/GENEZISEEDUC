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
    php8.3-fpm php8.3-cli php8.3-pgsql php8.3-sqlite3 php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-intl php8.3-zip php8.3-bcmath
```

L'application demande **PHP 8.3 minimum** : pas besoin de PHP 8.5 comme sur ta
machine, la version des dépôts Ubuntu convient.

`php8.3-pgsql` fournit `pdo_pgsql`, sans quoi l'application ne joint pas la
base Supabase. Vérifie-le avant d'aller plus loin :

```bash
php -m | grep -E '^(pdo_pgsql|curl|mbstring)$'
```

### Les trois réglages PHP à aligner

Dans `/etc/php/8.3/fpm/php.ini` **et** `/etc/php/8.3/cli/php.ini` — le premier
sert aux pages, le second à `php artisan` :

| Réglage | Valeur | Pourquoi |
| --- | --- | --- |
| `upload_max_filesize` | `512M` | La limite du bucket Supabase. PHP est à 2M par défaut : aucun rush d'interview ne passerait. |
| `post_max_size` | `520M` | Toujours au-dessus du précédent : il porte le fichier **et** le reste du formulaire. |
| `memory_limit` | `256M` | L'envoi se fait en flux, mais le reste de l'application respire mieux. |

```bash
sed -i -E 's/^upload_max_filesize = .*/upload_max_filesize = 512M/; s/^post_max_size = .*/post_max_size = 520M/; s/^memory_limit = .*/memory_limit = 256M/' \
    /etc/php/8.3/fpm/php.ini /etc/php/8.3/cli/php.ini
systemctl restart php8.3-fpm
```

Nginx a sa propre limite, plus basse que celle de PHP : elle se règle en
section 6. Les deux doivent monter ensemble, sinon Nginx coupe l'envoi avant
que PHP le voie.

Les certificats TLS n'ont rien à régler ici : Ubuntu fournit `ca-certificates`
et PHP s'en sert. C'est un problème propre à Windows, où il faut désigner un
fichier à `curl.cainfo`.

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
git clone https://github.com/joyjoy42/GENEZISEEDUC.git genezise
cd genezise

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.production.example .env
nano .env          # APP_URL, les clés DB_* et SUPABASE_* (tableau de bord Supabase)
php artisan key:generate
```

Vérifie que le serveur joint bien la base avant d'aller plus loin :

```bash
php artisan db:show
```

**La base est partagée, pas installée ici.** Elle vit dans Supabase : les
migrations et le questionnaire y ont déjà été joués depuis la machine de
développement. Il n'y a donc **ni fichier de base à créer, ni seeder à
relancer** sur le serveur.

```bash
php artisan migrate --force      # ne fait rien si tout est déjà appliqué
```

Si c'est un tout premier déploiement sur une base Supabase vide, ajoute le
questionnaire — sans lui le test d'orientation ne fonctionne pas :

```bash
php artisan db:seed --class=QuestionnaireSeeder --force
```

Le `BibliothequeSeeder`, lui, contient du contenu de démonstration avec des
passages entre crochets — **ne le lance pas en production**, saisis tes vraies
fiches depuis le back-office.

Crée ton compte, si tu n'en as pas déjà un dans la base partagée :

```bash
php artisan genezise:administrateur "Joy AKODEKOU" ton-email@exemple.com
```

Le mot de passe généré ne s'affiche qu'une fois. Note-le tout de suite.

## 5. Les permissions

Nginx et PHP tournent sous `www-data`. Il leur faut l'écriture sur le
stockage et le cache. La base est chez Supabase : plus aucun fichier de
données à protéger ici.

```bash
chown -R www-data:www-data /var/www/genezise/storage \
                           /var/www/genezise/bootstrap/cache
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

    # Doit rester au-dessus de post_max_size (section 3), sinon Nginx refuse
    # l'envoi d'un rush avant que PHP le voie.
    client_max_body_size 520M;

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

Les données sont dans Supabase, les médias dans son coffre. Rien d'important
ne vit plus sur le serveur : c'est le projet Supabase qu'il faut sauvegarder.

> **Le projet est au plan gratuit, qui ne fait aucune sauvegarde automatique.**
> Les sauvegardes quotidiennes commencent au plan Pro. Tant que tu restes au
> plan gratuit, la seule copie de ta base est celle que tu fabriques
> toi-même. Ne saute pas cette section.

### La base

`pg_dump` depuis le serveur, une fois par jour. Il faut la version 17, celle
du Postgres de Supabase : la 16 livrée par Ubuntu refuse de lire une base plus
récente.

```bash
install -d /usr/share/postgresql-common/pgdg
curl -fo /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc \n    https://www.postgresql.org/media/keys/ACCC4CF8.asc
echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" \n    > /etc/apt/sources.list.d/pgdg.list
apt update && apt install -y postgresql-client-17
```

Le mot de passe va dans `~/.pgpass`, pas dans la ligne de commande : tout le
monde peut lire `crontab -l` et la liste des processus.

```bash
echo 'aws-0-eu-west-1.pooler.supabase.com:5432:postgres:postgres.piaqxuxnlsdedffhahtc:TON-MOT-DE-PASSE' > ~/.pgpass
chmod 600 ~/.pgpass
```

`crontab -e` :

```cron
0 2 * * * pg_dump -h aws-0-eu-west-1.pooler.supabase.com -p 5432 -U postgres.piaqxuxnlsdedffhahtc -d postgres -Fc -f /var/backups/genezise-$(date +\%F).dump
0 3 * * * find /var/backups -name 'genezise-*.dump' -mtime +30 -delete
```

Le format `-Fc` se restaure avec `pg_restore`, table par table si besoin :

```bash
pg_restore -h ... -U ... -d postgres --clean --if-exists genezise-2026-10-26.dump
```

### Les médias

`pg_dump` ne copie **pas** le contenu du coffre : il n'en sauvegarde que la
liste. Les rushes d'interviews, eux, n'existent qu'à un seul endroit. Ce sont
des fichiers que tu as déjà sur ton ordinateur au montage — garde-les, c'est
aujourd'hui ta seule copie de secours.

### Les deux règles qui comptent

Sors les fichiers du serveur : un serveur qui meurt emporte ses sauvegardes
locales. Et teste une restauration **avant** d'en avoir besoin — une
sauvegarde jamais restaurée n'est pas une sauvegarde.

### Un piège du plan gratuit

Un projet Supabase gratuit est mis en pause après une semaine sans activité,
et le site tombe avec lui. Tant que la plateforme n'a pas de visiteurs
réguliers, ouvre-la de temps en temps, ou passe au plan Pro avant la mise en
ligne publique.

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
- **Passer au plan Pro de Supabase.** Le plan gratuit ne sauvegarde pas et met
  le projet en pause après une semaine sans activité (section 9). À trancher
  avant d'annoncer la plateforme publiquement.
- **Supprimer le compte `test@genezise.local`** s'il a été créé sur ce serveur.
