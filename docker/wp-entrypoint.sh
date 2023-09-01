#!/bin/bash
set -e

if wait-for-it.sh "${WORDPRESS_DB_HOST}" -t 60; then
  docker-entrypoint.sh apache2 -v
  wp core install \
    --allow-root \
    --title="${WP_TITLE}" \
    --admin_user="${ADMIN_USER}" \
    --admin_password="${ADMIN_PASS}" \
    --url="${WP_DOMAIN}" \
    --admin_email="${ADMIN_EMAIL}" \
    --skip-email

  # https://github.com/docker-library/wordpress/issues/436
  chown -R www-data:www-data "${DOCROOT_PATH}/wp-content/uploads"

  wp plugin is-installed akismet --allow-root && wp plugin uninstall akismet --allow-root --path="${DOCROOT_PATH}"
  wp plugin is-installed hello --allow-root && wp plugin uninstall hello --allow-root --path="${DOCROOT_PATH}"
  wp plugin install woocommerce --version="${WC_VERSION}" --activate --allow-root --path="${DOCROOT_PATH}"
  wp plugin install wp-crontrol --activate --allow-root --path="${DOCROOT_PATH}"
  wp plugin activate "${PLUGIN_NAME}" --allow-root --path="${DOCROOT_PATH}"

  # Custom setup instructions
  wp theme install storefront --version="4.5.2" --activate --allow-root --path="${DOCROOT_PATH}"
fi

exec "$@"
