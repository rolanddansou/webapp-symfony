# Makefile pour Symfony Backend Template
# Compatible Windows (PowerShell) et Linux/Mac
.PHONY: help install update db-setup db-create db-migrate db-fixtures db-reset test test-coverage analyze cs-check cs-fix cache-clear jwt-setup deploy-check deploy-prod deploy-staging

# Détection de l'OS
ifeq ($(OS),Windows_NT)
    RM = if exist $(1) rmdir /s /q $(1)
    MKDIR = if not exist $(1) mkdir $(1)
    PHP = php
    CONSOLE = php bin/console
    COMPOSER = composer
    NPM = npm
else
    RM = rm -rf $(1)
    MKDIR = mkdir -p $(1)
    PHP = php
    CONSOLE = php bin/console
    COMPOSER = composer
    NPM = npm
endif

##@ Aide

help: ## Afficher cette aide
	@echo "Makefile pour Symfony Backend Template"
	@echo ""
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Installation

install: ## Installation complète (composer + npm)
	@echo "📦 Installation des dépendances PHP..."
	$(COMPOSER) install
	@echo "📦 Installation des dépendances JavaScript..."
	$(NPM) install
	@echo "🔑 Génération des clés JWT..."
	@$(MAKE) jwt-setup
	@echo "✅ Installation terminée!"

update: ## Mise à jour des dépendances
	@echo "⬆️  Mise à jour des dépendances PHP..."
	$(COMPOSER) update
	@echo "⬆️  Mise à jour des dépendances JavaScript..."
	$(NPM) update
	@echo "✅ Mise à jour terminée!"

jwt-setup: ## Générer les clés JWT
	@echo "🔑 Génération des clés JWT..."
	$(CONSOLE) lexik:jwt:generate-keypair --skip-if-exists
	@echo "✅ Clés JWT générées!"

##@ Base de données

db-setup: db-create db-migrate db-fixtures ## Configuration complète de la base de données

db-create: ## Créer la base de données
	@echo "🗄️  Création de la base de données..."
	$(CONSOLE) doctrine:database:create --if-not-exists
	@echo "✅ Base de données créée!"

db-migrate: ## Exécuter les migrations
	@echo "🔄 Exécution des migrations..."
	$(CONSOLE) doctrine:migrations:migrate --no-interaction
	@echo "✅ Migrations exécutées!"

db-fixtures: ## Charger les fixtures de développement
	@echo "📝 Chargement des fixtures..."
	$(CONSOLE) doctrine:fixtures:load --no-interaction
	@echo "✅ Fixtures chargées!"

db-reset: ## Reset complet de la base de données
	@echo "⚠️  Reset de la base de données..."
	$(CONSOLE) doctrine:database:drop --force --if-exists
	@$(MAKE) db-setup
	@echo "✅ Base de données réinitialisée!"

db-validate: ## Valider le schéma Doctrine
	@echo "🔍 Validation du schéma..."
	$(CONSOLE) doctrine:schema:validate
	@echo "✅ Schéma validé!"

##@ Tests

test: ## Lancer tous les tests
	@echo "🧪 Exécution des tests..."
	$(PHP) bin/phpunit
	@echo "✅ Tests terminés!"

test-coverage: ## Lancer les tests avec couverture
	@echo "🧪 Exécution des tests avec couverture..."
	XDEBUG_MODE=coverage $(PHP) bin/phpunit --coverage-html var/coverage
	@echo "✅ Rapport de couverture généré dans var/coverage/"

test-unit: ## Lancer uniquement les tests unitaires
	@echo "🧪 Exécution des tests unitaires..."
	$(PHP) bin/phpunit tests/Unit
	@echo "✅ Tests unitaires terminés!"

test-feature: ## Lancer uniquement les tests fonctionnels
	@echo "🧪 Exécution des tests fonctionnels..."
	$(PHP) bin/phpunit tests/Feature
	@echo "✅ Tests fonctionnels terminés!"

##@ Qualité du code

analyze: ## Analyse statique avec PHPStan
	@echo "🔍 Analyse statique du code (PHPStan)..."
	$(PHP) vendor/bin/phpstan analyse --memory-limit=1G
	@echo "✅ Analyse terminée!"

cs-check: ## Vérifier le style de code
	@echo "🔍 Vérification du style de code..."
	$(PHP) vendor/bin/php-cs-fixer fix --dry-run --diff
	@echo "✅ Vérification terminée!"

cs-fix: ## Corriger automatiquement le style de code
	@echo "🔧 Correction du style de code..."
	$(PHP) vendor/bin/php-cs-fixer fix
	@echo "✅ Style de code corrigé!"

validate: test analyze cs-check ## Validation complète (tests + analyse + style)
	@echo "✅ Validation complète terminée!"

##@ Cache

cache-clear: ## Vider le cache
	@echo "🗑️  Vidage du cache..."
	$(CONSOLE) cache:clear
	@echo "✅ Cache vidé!"

cache-warmup: ## Préchauffer le cache
	@echo "🔥 Préchauffage du cache..."
	$(CONSOLE) cache:warmup
	@echo "✅ Cache préchauffé!"

##@ Assets

assets-install: ## Installer les assets publics
	@echo "📦 Installation des assets..."
	$(CONSOLE) assets:install public
	@echo "✅ Assets installés!"

assets-build: ## Build des assets JavaScript (production)
	@echo "🏗️  Build des assets..."
	$(NPM) run build
	@echo "✅ Assets buildés!"

assets-watch: ## Watch des assets JavaScript (développement)
	@echo "👀 Watch des assets..."
	$(NPM) run watch

##@ Déploiement

deploy-check: ## Vérifier avant déploiement
	@echo "🔍 Vérification pré-déploiement..."
	@echo "1. Validation du schéma Doctrine..."
	@$(MAKE) db-validate
	@echo "2. Lancement des tests..."
	@$(MAKE) test
	@echo "3. Analyse statique..."
	@$(MAKE) analyze
	@echo "4. Vérification du style de code..."
	@$(MAKE) cs-check
	@echo "✅ Prêt pour le déploiement!"

deploy-prod: ## Déploiement en production
	@echo "🚀 Déploiement en production..."
	@bash deploy/deploy.sh production

deploy-staging: ## Déploiement en staging
	@echo "🚀 Déploiement en staging..."
	@bash deploy/deploy.sh staging

##@ Utilitaires

secrets-list: ## Lister les secrets Symfony Vault
	@echo "🔐 Liste des secrets..."
	$(CONSOLE) secrets:list --reveal

secrets-generate: ## Générer les clés du Symfony Vault
	@echo "🔐 Génération des clés Vault..."
	$(CONSOLE) secrets:generate-keys

logs-tail: ## Suivre les logs en temps réel
	@echo "📋 Logs en temps réel..."
ifeq ($(OS),Windows_NT)
	@powershell -Command "Get-Content var/log/dev.log -Wait -Tail 50"
else
	@tail -f var/log/dev.log
endif

routes: ## Afficher toutes les routes
	@$(CONSOLE) debug:router

container: ## Afficher tous les services du container
	@$(CONSOLE) debug:container

##@ Développement

dev-server: ## Démarrer le serveur Symfony (nécessite Symfony CLI)
	@echo "🚀 Démarrage du serveur de développement..."
	symfony server:start -d
	@echo "✅ Serveur démarré sur http://localhost:8000"

dev-stop: ## Arrêter le serveur Symfony
	@echo "🛑 Arrêt du serveur..."
	symfony server:stop

dev-logs: ## Voir les logs du serveur Symfony
	@symfony server:log

