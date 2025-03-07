# **************************************************************************** #
#                                                                              #
#                                                         :::      ::::::::    #
#    Makefile                                           :+:      :+:    :+:    #
#                                                     +:+ +:+         +:+      #
#    By: phudyka <phudyka@student.42.fr>            +#+  +:+       +#+         #
#                                                 +#+#+#+#+#+   +#+            #
#    Created: 2025/03/03 15:03:33 by phudyka           #+#    #+#              #
#    Updated: 2025/03/07 16:31:31 by phudyka          ###   ########.fr        #
#                                                                              #
# **************************************************************************** #

NAME = camagru
DOCKER_COMPOSE = docker-compose

UP = $(DOCKER_COMPOSE) up -d
DOWN = $(DOCKER_COMPOSE) down
BUILD = $(DOCKER_COMPOSE) build
LOGS = $(DOCKER_COMPOSE) logs

DATA_DIR = ./data
UPLOAD_DIR = ./app/public/img/uploads
FILTER_DIR = ./app/public/img/filters

all: build up

build:
	@echo "🏗️ Construction des images Docker..."
	@mkdir -p $(DATA_DIR) $(UPLOAD_DIR) $(FILTER_DIR)
	@$(BUILD)
	@echo "✅ Images construites avec succès!"

up:
	@echo "🚀 Démarrage des conteneurs..."
	@$(UP)
	@echo "✅ Application lancée sur http://localhost"

down:
	@echo "🛑 Arrêt des conteneurs..."
	@$(DOWN)
	@echo "✅ Conteneurs arrêtés"

logs:
	@$(LOGS) -f

re: fclean all

clean:
	@echo "🧹 Nettoyage..."
	@$(DOWN)
	@echo "✅ Nettoyage terminé"

fclean: clean
	@echo "🧨 Nettoyage complet..."
	@$(DOWN) -v
	@rm -rf $(UPLOAD_DIR)/*
	@echo "✅ Tout a été supprimé"

.PHONY: all build up down logs re clean fclean