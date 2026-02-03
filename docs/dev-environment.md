## Development Environment

There is a [docker-compose file](../docker-compose.yml) in the root directory that allows for serving multiple development sites locally.

These are currently not using the real API but instead get their settings from the static JSON files included in the data folder.

The fake API is served by the [server.php](test/server.php) script and reads the corresponding [subdomain](data/WikiInfo-site1.json) from each request.

ElasticSearch in  docker compose environment uses non-shared index setup. Howerver the page should be refreshed a few time before ES content indexes got updated due to the lack of a dedicated job runner.

ElasticSearch index names are based on the wiki database name (not the domain). This is why indices appear in the format `{db_name}_content_first` and `{db_name}_general_first`, for example `mwdb_somedb1_content_first`.

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
curl -sS -H "Content-Type: application/json" -X POST -d '{}' "http://site1.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json"
curl -sS -H "Content-Type: application/json" -X POST -d '{}' "http://site2.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json"

# You can use `jq` to "pretty-print" the JSON response
curl -sS -H "Content-Type: application/json" -X POST -d '{}' "http://site1.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json" | jq .
curl -sS -H "Content-Type: application/json" -X POST -d '{}' "http://site2.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json" | jq .
```

Removing the installation:

```sh
docker compose down --volumes
```

Understanding special configuration for the `docker compose` environment:

When `$wwDockerCompose` is set some special settings are used. It is set from the environment variable `WBSTACK_DOCKER_COMPOSE` and is always true in the `docker compose` environment. Examples of this include disabling captchas, disabling the normally required email confirmation and automatically granting sysop rights to all users.

### Debugging Elastic

#### General overview of the cluster:

- Overall stats: http://localhost:9200/_stats
- Indices: http://localhost:9200/_cat/indices
- Aliases: http://localhost:9200/_aliases
- Entries in the content index (Items, Lexemes) for `site1.localhost`: http://localhost:9200/mwdb_somedb1_content_first/_search
