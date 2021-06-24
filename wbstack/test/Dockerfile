FROM php:7.4-apache

RUN set -eux; \
	a2enmod rewrite; \
	{ \
		echo '<Directory /var/www/html>'; \
		echo '  RewriteEngine On'; \
		echo '  RewriteRule . index.php [L]'; \
		echo '</Directory>'; \
	} > "$APACHE_CONFDIR/conf-available/api.conf";

RUN a2enconf api