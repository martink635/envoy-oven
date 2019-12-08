<?php return  [
    /**
     * Git repository
     *
     * If using private repos use the git@ format to avoid being
     * asked for password. For public Github repos use https protocl
     *
     * @example 'git@localhost:user/myrepo.git'
     */
    'repository' => '',

    /**
     * Deployment tactic
     *
     * Available tactics: html, laravel, bedrock
     *
     * @example 'html'
     */
    'deploy_tactic' => 'html',

    /**
     * Deployment server
     *
     * Connection information with the required paramaters
     *
     * @example '-i ~/.ssh/id_deployex deployer@host'
     * @example 'deployer@host'
     */
    'deploy_server' => '',

    /**
     * Deployment path
     *
     * Base path. Make sure it's absolute.
     *
     * @example '/var/www/project'
     */
    'deploy_path' => '',

    /**
     * Deployment public dir (optional)
     *
     * Public dir from the root folder (without the leading /).
     * Can be set to empty if public dir is root of the project
     *
     * @default 'public
     * @example 'web'
     */
    // 'public_dir' => 'web',

    /**
     * Shared subdirs (optional)
     *
     * Link this subdirs from shared to the current release
     */
    'shared_dirs' => [
        'storage',
    ],

    /**
     * Composer install (optional)
     *
     * Do we run composer install on this deployment
     *
     * @example false
     */
    // 'composer_install' => true,

    /**
     * NPM install (optional)
     *
     * Do we run npm install on this deployment
     *
     * @example false
     */
    // 'npm_install' => true,

    /**
     * Node version (optional)
     *
     * if Set changes node version using nvm to the specified value
     *
     * @example 11
     */
    // 'node_version' => false,

    /**
     * Keep releases count (optional)
     *
     * Number of releases to keep on remote, use -1 to disable purging
     *
     * @example -1
     * @example 10
     */
    // 'release_keep_count' => 5,

    /**
     * Default git branch (optional)
     *
     * @example 'develop'
     */
    // 'branch_default' => 'master',

    /**
     * Default subdirs (optional)
     *
     * Default deployment directories
     *  - releases: location where the releases are being stored
     *  - current: symlink location
     *  - shared: symlink shared from this location
     *
     */
    // 'dirs' => [
    //     'releases' => 'releases',
    //     'current' => 'current',
    //     'shared' => 'shared',
    // ]

    /**
     * Default services to reload (optional)
     *
     * Every service will be reloaded using /usr/sbin/service
     *
     */
    // 'reload_services' => ['nginx', 'php7.3-fpm'],
];