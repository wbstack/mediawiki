# Clears the git repo from fetched files and stuff..

echo "Clearing git repo (except the files that we maintain here)"

find ./ -mindepth 1 ! -regex '^./\(\.git\|wbstack\|Dockerfile\|robots.txt\|health.php\|composer.\w+.json\|\w+?[Ww]ikWiki.*\|\w+Settings.php\)\(/.*\)?' -delete