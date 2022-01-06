## Environment variables

- `MW_DB_SERVER_MASTER`: points to a writable mysql service
- `MW_DB_SERVER_REPLICA`: points to a readable mysql service
- `MW_REDIS_SERVER_WRITE`: points to a writable redis service
- `MW_REDIS_SERVER_READ`: points to a readable redis service
- `MW_REDIS_PASSWORD`
- `MW_MAILGUN_API_KEY`
- `MW_MAILGUN_DOMAIN`
- `MW_EMAIL_DOMAIN`
- `MW_RECAPTCHA_SITEKEY`
- `MW_RECAPTCHA_SECRETKEY`
- `PLATFORM_API_BACKEND_HOST`: points to an internal mode wbstack api service
- `MW_ELASTICSEARCH_HOST`: elasticsearch hostname
- `MW_ELASTICSEARCH_PORT`: elasticsearch port
- `MW_LOG_TO_STDERR`: set to "yes" to redirect all mediawiki logging to stderr (so it ends up in the kubernetes pod logs)
