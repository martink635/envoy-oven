# Simple deployment system using Laravel Envoy
"Zero-downtime" deployments with symlinks and rollbacks. Supports various scenarios for deploying Wordpress (Bedrock), Laravel and static html projects.

# Installation
```
composer global require laravel/envoy
```

Include with composer
```
composer require koterle/envoy-oven
```

or you can just copy the Envoy.blade.php and the config in the root of your project.

# Setup
Copy the sample config sample to root of your project.
```
cp vendor/koterle/envoy-oven/envoy.config.sample.php envoy.config.php
```
Most config options have a sensible default. The required options are:
```
'repository' => '',
'deploy_tactic' => '',
'deploy_server' => '',
'deploy_path' => '',
```

Check out the example config file for more information.

# Usage

## Deployment
If installed with composer run using:
```
envoy run deploy --path=./vendor/koterle/envoy-oven/Envoy.blade.php
```

If copied to root, run using:
```
envoy run deploy
```
```
Options
    --project        JSON encoded array with the project configuration
    --branch         Git branch (defaults to "master")
```

## Rollback
Rolls back to the previous release
```
envoy run rollback --path=./vendor/koterle/envoy-oven/Envoy.blade.php
```

# Changes
v2
 - Simplified configuration
 - Added setup/init tasks
 - We assume server has NVM installed for handling different node versions

# Credits
 - https://serversforhackers.com/
 - https://github.com/papertank/envoy-deploy

# License
This project is licensed under the MIT License.