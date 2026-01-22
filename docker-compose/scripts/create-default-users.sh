#!/bin/sh
set -eu

API_URL="${MW_API_URL}"
DOMAINS="${MW_DEFAULT_DOMAINS}"
USERNAME="${MW_DEFAULT_ADMIN_USERNAME}"
PASSWORD="${MW_DEFAULT_ADMIN_PASSWORD}"

# prefix all log messages with [default-users]
log() {
    echo "[default-users] $1"
}

# Wait for site1.localhost and site2.localhost to be available
wait_for_domain() {
    domain="$1"
    attempt=0
    while true; do
        if curl -fsS -H "Host: $domain" \
            "$API_URL?action=query&meta=siteinfo&format=json" >/dev/null 2>&1; then
            return 0
        fi
        sleep 2
    done
}

# Provision default users (admin and PlatformReservedUser) for a given domain using wbstackInit API
provision_default_users() {
    domain="$1"
    wait_for_domain "$domain"

    if response=$(curl -fsS -H "Host: $domain" \
        --data-urlencode "username=$USERNAME" \
        --data-urlencode "password=$PASSWORD" \
        "$API_URL?action=wbstackInit&format=json"); then
        if echo "$response" | grep -q '"success":"1"'; then
            log "Provisioned default users for $domain"
            return 0
        fi
        if echo "$response" | grep -qi 'User already existed'; then
            log "Default users already exists for $domain"
            return 0
        fi
        log "Unexpected response for $domain: $response"
        return 1
    fi

    log "Failed to created default users for $domain"
    return 1
}

# Provision default users for each domain
for domain in $DOMAINS; do
    provision_default_users "$domain"
done
