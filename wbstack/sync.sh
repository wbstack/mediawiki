# Syncs the whole git repo in the correct way

# Clears everything in the repo
./wbstack/clear.sh

# Fetches things from the web
./wbstack/docker-fetch.sh

# Removes some not needed things from the things fetched
./wbstack/less-files.sh

# Adds some extra files
cp ./wbstack/extra/* ./

# Does a composer install
