#!/usr/bin/env python3

from github import Github
import os
from typing import Any, Dict, List
import yaml

BASEDIR = os.path.dirname(os.path.realpath(__file__))
SOURCE_YAMLFILE = f"{BASEDIR}/wikiman.yaml"
DESTINATION_YAMLFILE = f"{BASEDIR}/pacman.yaml"
GITHUB_TOKEN = f"{BASEDIR}/.github"

codebases = {}
with open(SOURCE_YAMLFILE, "r") as filehandler:
    try:
        codebases = yaml.safe_load(filehandler)
    except yaml.YAMLError as exc:
        print(exc)

mediawiki_version = codebases['mediawikiVersion']
extensions = codebases['extensions']
skins = codebases['skins']

token = None
with open(GITHUB_TOKEN, "r") as filehandler:
    token = filehandler.readline().strip()

github = Github(token)

def get_mediawiki_branch_from_version(version: str) -> str:
    return f"REL{version.replace('.', '_')}"

def get_github_url_from_ref(github: Github, ref: str, repository: str):
    repo = github.get_repo(repository)
    # Note: The library doesn't say that ref can be used here, but the Github API allows it.
    commit = repo.get_commit(sha=ref)

    return f"https://codeload.github.com/{repository}/zip/{commit.sha}"

def make_artifact_entry(details: Dict[str, str]) -> Dict[str, Any]:
    name = details['name']

    if "repoName" in details.keys():
        artifactURL = get_github_url_from_ref(github, details['repoRef'], details['repoName'])
    elif "url" in details.keys():
        artifactURL = details['url']
    else:
        raise ValueError(f"'repoName' or 'url' key not specified for '{name}' in '{SOURCE_YAMLFILE}'")

    return {
        'name': name,
        'artifactUrl': artifactURL,
        'artifactLevel': 1,
        'destination': details['destination'],
    }

default_branch = get_mediawiki_branch_from_version(mediawiki_version)
output: List[Dict] = [make_artifact_entry({
    'name': 'mediawiki',
    'repoName': 'wikimedia/mediawiki',
    'repoRef': codebases.get('mediawikiRepoRef', default_branch),
    'destination': './dist'
    })]

def merge_dicts(a: Dict, b: Dict) -> Dict:
    c = dict(a)
    c.update(b)
    return c

output += [
    make_artifact_entry( merge_dicts(ext,{'destination': f"./dist/extensions/{ext['name']}", 'repoRef': ext.get('repoRef', default_branch)}) )
    for ext in extensions
    ]

output += [
    make_artifact_entry( merge_dicts(skin,{'destination': f"./dist/skins/{skin['name']}", 'repoRef': skin.get('repoRef', default_branch)}) )
    for skin in skins
    ]

# write out to the lock.yaml file
with open(DESTINATION_YAMLFILE, 'w') as outfile:
    yaml.dump(output, outfile, sort_keys=False)
