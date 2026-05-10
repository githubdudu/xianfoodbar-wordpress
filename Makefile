.PHONY: up down logs shell cache-clear ps perms db-shell composer-install

up:
	docker compose up -d

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f wordpress

shell:
	docker compose exec wordpress bash

perms:
	docker compose exec wordpress bash -c "\
		chmod -R 777 /var/www/html/wp-content/themes/mytheme/var && \
		chmod -R 777 /var/www/html/wp-content/themes/mytheme/public/build && \
		chmod -R 777 /var/www/html/wp-content/themes/mytheme/public/upload && \
		chmod 777 /var/www/html/wp-content/themes/mytheme && \
		chmod 777 /var/www/html/wp-content/themes/mytheme/src \
	"

cache-clear:
	docker compose exec wordpress bash -c "\
		php /var/www/html/wp-content/themes/mytheme/vendor/symfony/console/Resources/bin/console \
		--project-dir=/var/www/html/wp-content/themes/mytheme cache:clear --env=dev \
	"

db-shell:
	docker compose exec db mysql -u wordpress -pwordpress wordpress

composer-install:
	docker compose exec wordpress bash -c "\
		cd /var/www/html/wp-content/themes/mytheme && \
		composer install --no-interaction \
	"
