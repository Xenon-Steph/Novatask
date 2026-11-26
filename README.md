# NovaTask 2.0 — Laravel 13 + React 19 + MySQL

**Code source de base, contrôles syntaxiques réalisés mais tests d’intégration non exécutés dans cet environnement** : interface React et API Laravel, avec authentification par **sessions + cookies HttpOnly** via Sanctum, routes protégées, projets, tâches, Kanban (drag and drop), échéances, priorités, sous-tâches, statistiques et minuteur focus.

> Le ZIP contient le frontend complet et un `backend-overlay` à appliquer sur un Laravel officiel. Il **ne contient pas** `vendor/`, `node_modules/`, ni le squelette Laravel. Il faut installer les dépendances et exécuter les migrations. La collaboration par espaces et rôles est désormais codée ; les suggestions IA nécessitent une clé serveur. Les rappels e-mail, le mode hors ligne et la collaboration **temps réel** ne sont pas implémentés.

## Pré-requis

- PHP **8.3+**, Composer, extensions PHP pour MySQL, Node.js récent, npm, MySQL 8+
- Ne pas confondre `localhost` et `127.0.0.1` pour les cookies en local.

## Installation du backend

Depuis le répertoire contenant le dossier décompressé :

```bash
composer create-project laravel/laravel backend "^13.0"
cd backend
php artisan install:api
```

**Copier** tous les fichiers de `backend-overlay/` vers `backend/`, en conservant l'arborescence ; accepter le remplacement de `routes/api.php` et `routes/web.php` (ne pas copier `INSTALL_NOTES.txt` si inutile).

**Méthode recommandée** : depuis le répertoire racine `novatask/`, exécuter `python3 scripts/patch-laravel.py backend` pour ajouter automatiquement les relations User et `statefulApi`. Sinon, modifier manuellement `backend/app/Models/User.php` pour ajouter :

```php
public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Project::class);
}

public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Task::class);
}
```

Si le script a été utilisé, sauter cette modification. Sinon, dans `backend/bootstrap/app.php`, importer `Illuminate\Foundation\Configuration\Middleware` (normalement déjà présent) et activer **dans le bloc `withMiddleware` existant** :

```php
$middleware->statefulApi();
```

Exemple :

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->statefulApi();
})
```

Adapter `backend/.env` :

```dotenv
APP_URL=http://localhost:8000
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=novatask
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
SESSION_DOMAIN=null
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:8000
```

Créer une base `novatask` en UTF8MB4 dans MySQL, puis :

```bash
php artisan migrate
php artisan test
php artisan serve --host=localhost --port=8000
```

## Installation du frontend

Dans un deuxième terminal :

```bash
cd frontend
npm install
npm run build  # vérifier la compilation
npm run dev
```

Ouvrir **http://localhost:5173**. Vite transmet localement les requêtes `/api`, `/login`, `/register`, `/logout`, `/sanctum` vers Laravel ; les cookies restent same-origin pour le navigateur.

## Tester

1. Créer un compte (mot de passe de 10 caractères minimum, majuscule/minuscule/chiffre).
2. Créer un projet avec une couleur.
3. Créer des tâches, les éditer, les faire glisser entre les trois colonnes.
4. Ajouter des sous-tâches depuis le bouton Détails.
5. Déclencher un focus et laisser son minuteur se terminer pour cumuler le temps.
6. Se déconnecter, vérifier que l'API refuse les requêtes non authentifiées.

## Routes API

| Méthode | Endpoint | Objet |
|--|--|--|
| GET | `/api/me` | Profil courant |
| GET / POST | `/api/projects` | Projets |
| PATCH / DELETE | `/api/projects/{project}` | Modification / suppression |
| GET / POST | `/api/tasks` | Liste paginée / création |
| PATCH / DELETE | `/api/tasks/{task}` | Modification / suppression |
| GET | `/api/stats` | Statistiques |
| POST | `/api/tasks/{task}/focus` | Créditer une session de focus validée côté client |
| POST | `/api/tasks/{task}/subtasks` | Ajouter une étape |
| PATCH / DELETE | `/api/tasks/{task}/subtasks/{subtask}` | Modifier / supprimer une étape |

## Sécurité, limitations et phase production

- Les ressources sont vérifiées par leur `user_id` côté serveur ; les identifiants de projet sont validés selon le propriétaire.
- Sessions Sanctum, protection CSRF, hashage Laravel, régénération de session, validation serveur et limitation de débit pour login/register.
- Les 100 premières tâches sont chargées dans le Kanban. **Pour un grand volume**, implémenter infinite scroll / pagination par colonne et filtres SQL : actuellement le Kanban ne montre que la première page.
- Le temps de focus est **déclaré par le client** et n'est pas une mesure fiable d'activité : utiliser sessions horodatées et vérifiées côté serveur pour une facturation, une compétition, ou des statistiques anti-triche.
- **Avant déploiement public** : HTTPS + `SESSION_SECURE_COOKIE=true`, `APP_DEBUG=false`, secrets gérés par variables d'environnement, sauvegardes et restaurations testées, mails transactionnels et parcours de réinitialisation du mot de passe, vérification e-mail, monitoring, gestion fine des permissions si collaboration, politique de rétention RGPD et tests automatisés.
- Le navigateur doit rester actif pour que le minuteur local avance de manière fiable ; il n'y a pas de notifications push ni de synchronisation multi-onglets garantie.

## Roadmap produit

**V1 (base reprise dans ce ZIP)** — inscription/connexion, CRUD projets/tâches/sous-tâches, Kanban, recherche, filtres, statistiques, focus.

**V1.5** — récurrence, rappels email, calendrier, tags, pièces jointes protégées, reset password, email verification, undo/soft delete, test automatisés.

**V2** — équipe et rôles (ajoutés), commentaires (à faire), notifications temps réel, calendrier connecté, mode hors ligne PWA, historique/audit, synchronisation multi-appareils, assistance IA sous consentement.


## NovaTask 2.0 — fonctionnalités ajoutées

- **Espaces** : sélecteur "Personnel / équipes" dans la barre latérale ; création d'équipes et ajout de membres inscrits via leur adresse e-mail.
- **Rôles** : `owner`, `admin`, `member`, `viewer`. Les membres peuvent créer et éditer les projets/tâches de leur espace ; les lecteurs consultent seulement ; propriétaires et admins gèrent les membres.
- **Protection croisée** : une tâche/projet personnel appartient à un seul utilisateur, les ressources d'équipe sont filtrées par membership et `workspace_id`, y compris lors des modifications et des sous-tâches.
- **IA** : dans "Assistant IA", saisissez un objectif puis vérifiez jusqu'à cinq propositions avant de les ajouter. **Le service ne fabrique pas de réponse hors ligne** : si la clé n'est pas configurée, l'API renvoie 503. Les informations saisies sont envoyées au fournisseur externe configuré : ne pas saisir d'informations personnelles/confidentielles sans avoir évalué la politique du service.
- **Focus** : le démarrage et la fin de session sont enregistrés en base ; les minutes sont calculées par Laravel, et ne peuvent plus être arbitrairement déclarées par le navigateur. Pauser le compteur ne pause pas le temps serveur ; pas encore de reprise fiable entre appareils.
- **Audit** : journal minimal d'ajout de membre en base ; journalisation complète des actions à construire avant utilisation réglementée.

### Variables d'environnement IA

Ajouter au fichier `backend/.env` (jamais dans le bundle React) :

```dotenv
NOVATASK_AI_KEY=VOTRE_CLE_SERVEUR
NOVATASK_AI_MODEL=UN_MODELE_COMPATIBLE_CHAT_COMPLETIONS
```

Ces paramètres sont lus dans `backend/config/services.php`. La requête utilise une URL fixe `https://api.openai.com/v1/chat/completions`, `timeout(18)` et une limite de débit de six requêtes/minute par utilisateur/IP selon la configuration Laravel. **Les appels IA ont un coût possible**. Après changement de `.env`, utiliser `php artisan config:clear`. Ne jamais committer la clé. L'API filtre et borne les champs JSON renvoyés par le modèle, mais il reste indispensable de relire les suggestions.

### Endpoints V2

| Méthode | Route | Usage |
|---|---|---|
| GET / POST | `/api/workspaces` | Lister / créer une équipe |
| GET / POST | `/api/workspaces/{workspace}/members` | Membres / ajout d'un utilisateur inscrit |
| DELETE | `/api/workspaces/{workspace}/members/{user}` | Retirer un membre autorisé |
| POST | `/api/ai/plan` | Générer des suggestions (sans écriture automatique) |
| POST | `/api/tasks/{task}/focus/start` | Démarrer une session horodatée |
| POST | `/api/tasks/{task}/focus/{session}/finish` | Terminer et créditer le temps serveur |

Les routes de tâches, projets, statistiques et focus acceptent `workspace_id` comme paramètre query. Son absence sélectionne l'espace personnel. Il n'est **pas possible** d'accéder aux tâches d'un autre espace en changeant un simple identifiant : le serveur vérifie le rôle et l'appartenance.

### Tests / CI

Les fichiers `tests/Feature/WorkspaceSecurityTest.php` et `FocusSecurityTest.php` couvrent l'isolement des données, les permissions, la non-divulgation des tâches d'autrui, la validation d'un projet étranger et l'interdiction d'un crédit Focus arbitraire. L'intégration continue de référence se trouve dans `.github/workflows/ci.yml`.

**Vérifications effectuées dans l'environnement de création :** `php -l` sur tous les fichiers PHP ; parsing/transpilation JSX via TypeScript ; archive ZIP vérifiée. **Non vérifié :** `php artisan test`, `npm run build`, requêtes MySQL réelles et navigateur de bout en bout (Composer absent, cache npm insuffisant). Il faudra exécuter les commandes ci-dessus dans votre installation et corriger les défauts éventuels avant déploiement.

### Déploiement public

1. Construire sur une machine disposant de PHP 8.3+, Composer, Node.js 22+ et MySQL 8+.
2. Sur le serveur : cloner les sources, installer Laravel officiel, exécuter `php artisan install:api`, copier `backend-overlay/`, puis `python3 scripts/patch-laravel.py backend`.
3. Configurer `backend/.env` d'après `deploy/production.env.example`, avec un mot de passe MySQL non-root, `APP_DEBUG=false`, une clé Laravel générée sur le serveur, et une connexion TLS valide.
4. Exécuter `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` (si applicable), `npm ci` si lockfile généré, puis `npm run build`.
5. Servir les fichiers React compilés depuis `frontend/dist` et transférer les routes `/api`, `/sanctum`, `/login`, `/register`, `/logout` à Laravel via Nginx/PHP-FPM. Exemple : `deploy/nginx.conf.example` (à adapter aux chemins, au socket PHP-FPM et au HTTPS).
6. Tester la restauration des backups et configurer alertes, monitoring, politique de rétention, surveillance du coût IA, permissions et mise à jour des dépendances. Activer HTTPS + HSTS une fois le domaine vérifié.

### Limites connues / prochains incréments

- Les tâches sont paginées à 100 par réponse ; le Kanban affiche la première page. Ajouter pagination infinie, recherches côté serveur et virtualisation pour plus de 100 tâches.
- L'ajout d'équipe suppose que l'utilisateur destinataire a déjà créé un compte ; **pas d'invitation e-mail ni d'acceptation opt-in**. Ne pas présenter ce flux comme des invitations sécurisées.
- Pas encore de notifications push, tâches récurrentes, assignation individuelle, commentaires, coédition temps réel, mode hors ligne PWA, pièces jointes, synchronisation calendrier, export RGPD complet, suppression de compte, vérification d'e-mail ni réinitialisation du mot de passe. Leur développement reste nécessaire avant une mise en production publique grand public.
- L'authentification repose sur les sessions Sanctum ; `SESSION_SECURE_COOKIE=true` en production. Pour un site public prévoir vérification e-mail, MFA, rotation de sessions, WAF/anti-abus, et journaux d'audit complets.
- L'IA n'est pas un agent autonome : elle renvoie des propositions à approuver ; aucune tâche n'est créée sans clic explicite.
