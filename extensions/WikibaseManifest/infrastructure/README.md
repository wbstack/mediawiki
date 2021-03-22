This provides a quick and automated way to configure a test system for a 'WikibaseManifest' enabled Wikibase instance

### Prerequisites

We use [ansible](https://docs.ansible.com/ansible/latest/index.html) to manipulate remote servers via SSH - essentially automating what you'd otherwise do "by hand" in a step-by-step approach. Make sure to have version >=2.8, e.g. by installing via `pip`:
```
$ pip install ansible

$ ansible --version
ansible 2.9.6
```

You need to be in possession of an SSH private key for which there is an associated user that is authorized to perform the operations.

### Inventory

The file `inventory.yml` contains a single host, which is the target for the test system setup:
 * `wikibase-product-testing.wmflabs.org` - the project's official cloud VPS test instance

### Setup

Set up your VPS instance on https://horizon.wikimedia.org and a web proxy to reach it from the internet, then:
```sh
$ cd extensions/WikibaseManifest/infrastructure
$ ansible-galaxy install -r requirements.yml
$ ansible-playbook setup.yml --limit <hostname>.wikidata-dev.eqiad.wmflabs
```

Once the setup process has completed, you can access the newly installed Wikibase test system via web proxy, e.g. `https://wikibase-product-testing.wmflabs.org/`.


### Cleanup

The `cleanup.yml` playbook removes most of the changes that the setup has caused:

```sh
$ cd extensions/WikibaseManifest/infrastructure
$ ansible-playbook cleanup.yml --limit <hostname>.wikidata-dev.eqiad.wmflabs
```
