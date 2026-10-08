#!/usr/bin/env python3

import argparse
from typing import Any, Dict, List
from github import Github, Auth
import yaml

argument_parser = argparse.ArgumentParser()
argument_parser.add_argument(
    'target',
    help='directory containing wikiman.yaml and pacman.yaml',
)
argument_parser.add_argument(
    '--patches',
    choices=['prompt', 'keep', 'drop'],
    default='prompt',
    help='prompt to review patches, or keep/drop every patch',
)
arguments = argument_parser.parse_args()

TARGET_DIR = arguments.target
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

token = None
with open(GITHUB_TOKEN_FILE, "r") as filehandler:
    token = filehandler.readline().strip()

github_client = Github(auth=Auth.Token(token))

def get_mediawiki_branch_from_version(version: str) -> str:
    return f"REL{version.replace('.', '_')}"

def get_github_url_from_ref(github: Github, ref: str, repository: str):
    repo = github.get_repo(repository)
    # Note: The library doesn't say that ref can be used here, but the Github API allows it.
    commit = repo.get_commit(sha=ref)

    return f"https://codeload.github.com/{repository}/zip/{commit.sha}"

def should_keep_patch(details: Dict[str, Any], patch_url: str) -> bool:
    if arguments.patches != 'prompt':
        return arguments.patches == 'keep'

    name = details['name']
    repository = f" ({details['repoName']})" if 'repoName' in details else ''

    print()
    print(f"{name}{repository} specifies this patch:")
    print(f"    {patch_url}")
    print()
    print(
        "ACTION REQUIRED: Review whether this patch is still required. "
        "E.g., has the patch been merged into upstream?"
    )
    print()

    while True:
        answer = input("Keep this patch? [y/n]: ").strip().lower()
        if answer in ('y', 'n'):
            return answer == 'y'
        print("Please answer 'y' or 'n'.")

def make_artifact_entry(details: Dict[str, Any], extra_remove: List[str]) -> Dict[str, Any]:
    name: str = details['name']
    patches: List[Dict[str, str]] = details.get('patches', [])

    if "repoName" in details.keys():
        repo_ref = details.get('repoRef', mediawiki_branch)
        artifact_url = get_github_url_from_ref(github_client, repo_ref, details['repoName'])
    elif "url" in details.keys():
        artifact_url = details['url']
    else:
        raise ValueError(f"'repoName' or 'url' key not specified for '{name}' in '{SOURCE_YAMLFILE}'")

    artifact_entry = {
        'name': name,
        'artifactUrl': artifact_url,
        'artifactLevel': 1,
        'destination': details['destination'],
        'remove' : extra_remove + details.get('remove', []),
    }

    patch_urls = [patch['url'] for patch in patches if should_keep_patch(details, patch['url'])]
    if patch_urls:
        artifact_entry['patchUrls'] = patch_urls

    return artifact_entry

# pylint: disable=too-many-ancestors
class FixedIndentingDumper(yaml.Dumper):
    """Fix for making yaml list output pass linting https://stackoverflow.com/a/39681672"""
    def increase_indent(self, flow=False, indentless=False):
        return super().increase_indent(flow, False)

mediawiki_branch = get_mediawiki_branch_from_version(mediawiki_version) # pylint: disable-msg=C0103
remove_from_all = codebases.get('removeFromAll', [])
output: List[Dict] = [make_artifact_entry({
        'name': 'mediawiki',
        'repoName': 'wikimedia/mediawiki',
        'repoRef': codebases.get('mediawikiRepoRef', mediawiki_branch),
        'destination': './dist',
        'remove': codebases.get('mediawikiRemove', []),
        'patches': codebases.get('mediawikiPatches', []),
    }, remove_from_all)]

output += [
    make_artifact_entry( {**ext, 'destination': f"./dist/extensions/{ext['name']}"}, remove_from_all )
    for ext in extensions
]

output += [
    make_artifact_entry( {**skin, 'destination': f"./dist/skins/{skin['name']}"}, remove_from_all )
    for skin in skins
]

# write out to the lock.yaml file
with open(DESTINATION_YAMLFILE, 'w') as outfile:
    yaml.dump(output, outfile, sort_keys=False, Dumper=FixedIndentingDumper, default_flow_style=False)
