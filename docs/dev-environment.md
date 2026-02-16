## Development Environment

There is a [docker-compose file](../docker-compose.yml) in the root directory that allows for serving multiple development sites locally.

These are currently not using the real API but instead get their settings from static JSON files in [docker-compose/fake-api](../docker-compose/fake-api/).

The fake API is served by [index.php](../docker-compose/fake-api/index.php) and reads the JSON matching the requested subdomain (for example [WikiInfo-site1.json](../docker-compose/fake-api/WikiInfo-site1.json)).

> [!NOTE]
> You may find you have to refresh the page a few times before changes are reflected in Elasticsearch. Unlike [wmde/wbaas-deploy](https://github.com/wmde/wbaas-deploy/), this setup doesn't have a dedicated job runner. Jobs queued up, such as ones from CirrusSearch and WikibaseCirrusSearch, are completed as part of web requests (see [wbstack/src/Settings/LocalSettings.php#L147-L151](https://github.com/wbstack/mediawiki/blob/ebac07a4a4096d8fd973ebd43ebe342f34b87803/dist-persist/wbstack/src/Settings/LocalSettings.php#L147-L151)), so refreshing the page ensures that all jobs in the queue are run.

### Start the dev environment

```sh
docker compose up --build
```

> [!NOTE]
> It's important to include the `--build` flag after making any significant changes to `dist`. The `Dockerfile` contains important maintenance scripts (e.g., `rebuildLocalisationCache.php`) that will only run when the container image is rebuilt.

Wait until both sites are accessible:

 - http://site1.localhost:8001/wiki/Main_Page
 - http://site2.localhost:8001/wiki/Main_Page

 You may need to add an entry to your `hosts` file:

 ```
 127.0.0.1 site1.localhost site2.localhost
 ```

 Once the sites are accessible you can perform secondary setup (_The request takes a while to execute_):

 ```sh
curl -sS -H "Content-Type: application/json" -X POST -d '{}' "http://site1.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json" | jq
curl -sS -H "Content-Type: application/json" -X POST -d '{}' "http://site2.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json" | jq
```

Removing the installation:

```sh
docker compose down --volumes
```

Understanding special configuration for the `docker compose` environment:

When `$wwDockerCompose` is set some special settings are used. It is set from the environment variable `WBSTACK_DOCKER_COMPOSE` and is always true in the `docker compose` environment. Examples of this include disabling captchas, disabling the normally required email confirmation and automatically granting sysop rights to all users.

### Elasticsearch

- Overall stats: http://localhost:9200/_stats
- Indices: http://localhost:9200/_cat/indices
- Aliases: http://localhost:9200/_aliases
- Entries in the content index (Items, Lexemes) for `site1.localhost`: http://localhost:9200/mwdb_somedb1_content_first/_search

In [wmde/wbaas-deploy](https://github.com/wmde/wbaas-deploy/) Elasticsearch is configured so all Wikis share the same indices. In contrast, this docker compose environment is configured so each wiki has its own indices. This is how Elasticsearch was configured when this docker composer development environment was created. As MediaWiki isn't creating new indices, whether Elasticsearch uses shared indices via aliases or not is inconsequential. Investing time to change this is likely not worth it and having separate indices is a simpler setup.

The index names are based on the MediaWiki database name (not the domain). This is why indices appear in the format `{db_name}_content_first` and `{db_name}_general_first`, for example `mwdb_somedb1_content_first`.
