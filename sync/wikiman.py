#!/usr/bin/env python3

import os
from typing import Any, Dict, List
from github import Github
import yaml

BASEDIR = os.path.dirname(os.path.realpath(__file__))
SOURCE_YAMLFILE = f"{BASEDIR}/wikiman.yaml"
DESTINATION_YAMLFILE = f"{BASEDIR}/pacman.yaml"
GITHUB_TOKEN_FILE = f"{BASEDIR}/.github"

codebases = {}
with open(SOURCE_YAMLFILE, "r") as filehandler:
    try:
        codebases = yaml.safe_load(filehandler)
    except yaml.YAMLError as exc:
        print(exc)

mediawiki_version = codebases['mediawikiVersion']
extensions = codebases['extensions']
skins = codebases['skins']

TOKEN = None
with open(GITHUB_TOKEN_FILE, "r") as filehandler:
    TOKEN = filehandler.readline().strip()

githubClient = Github(TOKEN)

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
        artifact_url = get_github_url_from_ref(githubClient, details['repoRef'], details['repoName'])
    elif "url" in details.keys():
        artifact_url = details['url']
    else:
        raise ValueError(f"'repoName' or 'url' key not specified for '{name}' in '{SOURCE_YAMLFILE}'")

    entry = {
        'name': name,
        'artifactUrl': artifact_url,
        'artifactLevel': 1,
        'destination': details['destination'],
    }
    if 'remove' in details.keys():
        entry['remove'] = details['remove']
    return entry

default_branch = get_mediawiki_branch_from_version(mediawiki_version)
output: List[Dict] = [make_artifact_entry({
    'name': 'mediawiki',
    'repoName': 'wikimedia/mediawiki',
    'repoRef': codebases.get('mediawikiRepoRef', default_branch),
    'destination': './dist'
    })]

output += [
    make_artifact_entry( {**ext,'destination': f"./dist/extensions/{ext['name']}", 'repoRef': ext.get('repoRef', default_branch)} )
    for ext in extensions
    ]

output += [
    make_artifact_entry( {**skin,'destination': f"./dist/skins/{skin['name']}", 'repoRef': skin.get('repoRef', default_branch)} )
    for skin in skins
    ]

# write out to the lock.yaml file
with open(DESTINATION_YAMLFILE, 'w') as outfile:
    yaml.dump(output, outfile, sort_keys=False)
