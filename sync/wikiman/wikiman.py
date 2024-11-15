#!/usr/bin/env python3

import sys
from typing import Any, Dict, List
from github import Github
import yaml

TARGET_DIR = sys.argv[1]
SOURCE_YAMLFILE = f"{TARGET_DIR}/wikiman.yaml"
DESTINATION_YAMLFILE = f"{TARGET_DIR}/pacman.yaml"
# TODO: This .github token should move to to top level
GITHUB_TOKEN_FILE = f"{TARGET_DIR}/sync/.github"

codebases = {}
with open(SOURCE_YAMLFILE, "r") as filehandler:
    codebases = yaml.safe_load(filehandler)

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

def make_artifact_entry(details: Dict[str, str], extra_remove: List[str]) -> Dict[str, Any]:
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
        'remove' : extra_remove + details.get('remove', []),
    }

    if "patchUrls" in details.keys():
        entry['patchUrls'] = []
        for patch_url in details['patchUrls']:
            entry['patchUrls'].append(patch_url)

    return entry

# pylint: disable=too-many-ancestors
class FixedIndentingDumper(yaml.Dumper):
    """Fix for making yaml list output pass linting https://stackoverflow.com/a/39681672"""
    def increase_indent(self, flow=False, indentless=False):
        return super().increase_indent(flow, False)

default_branch = get_mediawiki_branch_from_version(mediawiki_version)
remove_from_all = codebases.get('removeFromAll', [])
output: List[Dict] = [make_artifact_entry({
    'name': 'mediawiki',
    'repoName': 'wikimedia/mediawiki',
    'repoRef': codebases.get('mediawikiRepoRef', default_branch),
    'destination': './dist',
    'remove': codebases.get('mediawikiRemove', [])
    }, remove_from_all)]

output += [
    make_artifact_entry( {**ext,'destination': f"./dist/extensions/{ext['name']}", 'repoRef': ext.get('repoRef', default_branch)}, remove_from_all )
    for ext in extensions
    ]

output += [
    make_artifact_entry( {**skin,'destination': f"./dist/skins/{skin['name']}", 'repoRef': skin.get('repoRef', default_branch)}, remove_from_all )
    for skin in skins
    ]

# write out to the lock.yaml file
with open(DESTINATION_YAMLFILE, 'w') as outfile:
    yaml.dump(output, outfile, sort_keys=False, Dumper=FixedIndentingDumper, default_flow_style=False)
