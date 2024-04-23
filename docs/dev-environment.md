## Development Environment

There is a [docker-compose file](../docker-compose.yml) in the root directory that allows for serving multiple development sites locally.

These are currently not using the real api but rather gets their settings from the static json files included in the data folder.

The fake api is served by the [server.php](test/server.php) script and reads the corresponding [subdomain](data/WikiInfo-site1.json) from each request.


### Start the dev environment

```sh
docker compose up --build
```

> **_Note:_** It's important to include the `--build` flag after making any significant changes to `dist`. The `Dockerfile` contains important maintenance scripts (e.g., `rebuildLocalisationCache.php`) that will only run when the container image is rebuilt.

Wait until both sites are accessible:

 - http://site1.localhost:8001/wiki/Main_Page
 - http://site2.localhost:8001/wiki/Main_Page

 You may need to add an entry to your `hosts` file:

 ```
 127.0.0.1 site1.localhost site2.localhost
 ```

 Once the sites are accessible you can perform secondary setup (_The request takes a while to execute_):

 ```sh
curl -l -X POST "http://site1.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json"
curl -l -X POST "http://site2.localhost:8001/w/api.php?action=wbstackElasticSearchInit&format=json"
```

Removing the installation:

```sh
docker compose down --volumes
```

### Updating localizations

After modifying `i18n` files, run the following to update localization caches:

```sh
docker compose run --rm -it mediawiki bash -c "WBS_DOMAIN=maint php w/maintenance/rebuildLocalisationCache.php --lang=en --force"
```

### Debugging Elastic

General overview of the cluster

```
http://localhost:9200/_stats
```

Entries in the content index (Items, Lexemes) for `site1.localhost` can be found by going to the following url

```
http://localhost:9200/site1.localhost_content_first/_search
```
