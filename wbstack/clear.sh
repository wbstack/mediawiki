# Clears the git repo from fetched files and stuff..
find ./ -mindepth 1 ! -regex '^./\(\.git\|wbstack\)\(/.*\)?' -delete