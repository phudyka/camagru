# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: phudyka <phudyka@student.42.fr>            +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2025/03/03 15:03:33 by phudyka           #+#    #+#              #
#    Updated: 2025/03/03 15:29:19 by phudyka          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

# Makefile pour le projet Camagru

# Variables
NAME = camagru
DOCKER_COMPOSE = docker-compose

# Commandes Docker
UP = $(DOCKER_COMPOSE) up -d
DOWN = $(DOCKER_COMPOSE) down
BUILD = $(DOCKER_COMPOSE) build
LOGS = $(DOCKER_COMPOSE) logs

# Dossiers
DATA_DIR = ./data
UPLOAD_DIR = ./app/public/img/uploads
FILTER_DIR = ./app/public/img/filters

# Règles
all: build up

# Construction des images Docker
build:
	@echo "🏗️ Construction des images Docker..."
	@mkdir -p $(DATA_DIR) $(UPLOAD_DIR) $(FILTER_DIR)
	@$(BUILD)
	@echo "✅ Images construites avec succès!"

# Démarrage des conteneurs
up:
	@echo "🚀 Démarrage des conteneurs..."
	@$(UP)
	@echo "✅ Application lancée sur http://localhost"

# Arrêt des conteneurs
down:
	@echo "🛑 Arrêt des conteneurs..."
	@$(DOWN)
	@echo "✅ Conteneurs arrêtés"

# Affichage des logs
logs:
	@$(LOGS) -f

# Reconstruction complète
re: fclean all

# Nettoyage (arrête les conteneurs mais garde les volumes)
clean:
	@echo "🧹 Nettoyage..."
	@$(DOWN)
	@echo "✅ Nettoyage terminé"

# Nettoyage complet (supprime aussi les volumes et données générées)
fclean: clean
	@echo "🧨 Nettoyage complet..."
	@$(DOWN) -v
	@rm -rf $(UPLOAD_DIR)/*
	@echo "✅ Tout a été supprimé"

# Phony targets
.PHONY: all build up down logs re clean fclean