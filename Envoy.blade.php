@setup
    // We can load the config via a project argument JSON encoded string or
    // via the envoy.config.php file
    if (isset($project)) {
        $project = json_decode($project, true);
    } else {
        $project = include('envoy.config.php');
    }

    if (!isset($project['deploy_server'])) {
        throw new Exception('Deployment server is not set');
    }

    if (!isset($project['deploy_tactic'])) {
        throw new Exception('Deployment tactic is not set');
    }

    if (!isset($project['deploy_path'])) {
        throw new Exception('Deployment path is not set');
    }

    if (substr($project['deploy_path'], 0, 1) !== '/' ) {
        throw new Exception('Deploy path does not begin with /');
    }

    if (!isset($project['repository'])) {
        throw new Exception('Repository is not set');
    }

    // Append / at the end of the path
    $base_dir = rtrim($project['deploy_path'], '/') . '/';

    // Setting some sensible defaults
    $repository = $project['repository'];
    $releases_dir = $base_dir . (isset($project['dirs']['releases']) ? $project['dirs']['releases'] : 'releases');
    $current_dir = $base_dir . (isset($project['dirs']['current']) ? $project['dirs']['current'] : 'current');
    $shared_dir = $base_dir . (isset($project['dirs']['shared']) ? $project['dirs']['shared'] : 'shared');
    $release = date("YmdHis");

    if (! $branch) {
        $branch = isset($project['branch_default']) ? $project['branch_default'] : 'master';
    }

    $public_dir = isset($project['public_dir']) ? $project['public_dir'] : 'public';
    $composer_install = isset($project['composer_install']) ? $project['composer_install'] : true;
    $npm_install = isset($project['npm_install']) ? $project['npm_install'] : true;
    $release_keep_count = isset($project['release_keep_count']) ? $project['release_keep_count'] : 5;
    $node_version = isset($project['node_version']) ? $project['node_version'] : false;
    $reload_services = isset($project['reload_services']) ? $project['reload_services'] : ['nginx', 'php7.3-fpm'];
@endsetup

@servers(['web' => $project['deploy_server']])

@story('deploy', [ 'on' => 'web' ])
    fetch
    composer
    npm
    permissions
    {{ $project['deploy_tactic'] }}
    symlink
    reload
    purge_old
@endstory

@task('fetch')
    echo 'Deploying from branch {{ $branch }}'

    echo 'Preparing directories: {{ $base_dir }}';
    [ -d {{ $releases_dir }} ] || mkdir -p {{ $releases_dir }};
    cd {{ $releases_dir }};

    git clone -b {{ $branch }} --depth=1 {{ $repository }} {{ $release }};
@endtask

@task('composer')
    # run composer install if needed
    @if ($composer_install)
        echo 'Installing composer dependencies';
        cd {{ $releases_dir }}/{{ $release }};
        composer install --prefer-dist --no-scripts --no-dev -q -o;
    @endif
@endtask

@task('npm')
    # run npm install if needed
    @if ($npm_install)
        echo 'Installing npm dependencies';
        cd {{ $releases_dir }}/{{ $release }};

        @if ($node_version)
            . ~/.nvm/nvm.sh;
            . ~/.profile;
            . ~/.bashrc;
            nvm use {{ $node_version }};
        @endif

        npm ci --silent;
    @endif
@endtask

@task('permissions')
    echo 'Setting up permissions'
    cd {{ $releases_dir }};
    chgrp -R www-data {{ $release }};
    chmod -R ug+rwx {{ $release }};
@endtask


@task('symlink')
    # Symlink the latest release to the current directory
    echo 'Linking current release';
    ln -nfs {{ $releases_dir }}/{{ $release }}/{{ $public_dir }} {{ $current_dir }};
@endtask

@task('reload')
    @foreach ($reload_services as $service)
        echo 'Reloading: {{ $service }}';
        sudo /usr/sbin/service {{ $service }} reload
    @endforeach
@endtask

@task('purge_old')
    @if ($release_keep_count != -1)
        echo 'Purging old releases';
        # This will list our releases by modification time and delete all but the 5 most recent
        ls -dt {{ $releases_dir }}/* | tail -n +{{ $release_keep_count + 1 }} | xargs -d '\n' rm -rf;
    @endif
@endtask

@task('rollback', [ 'on' => 'web' ])
    echo 'Rolling back to previous release';
    cd {{ $releases_dir }}
    ln -nfs $(find {{ $releases_dir }} -maxdepth 1 -name "20*" | sort  | tail -n 2 | head -n1) {{ $current_dir }}
	echo "Rolled back to $(find . -maxdepth 1 -name "20*" | sort  | tail -n 2 | head -n1)"
@endtask

@task('html')
    # html deploy strategy
    echo 'Html app deployment';
    cd {{ $releases_dir }}/{{ $release }}

    @if ($node_version)
        . ~/.nvm/nvm.sh;
        . ~/.profile;
        . ~/.bashrc;
        nvm use {{ $node_version }};
    @endif

    npm run production;
@endtask

@task('bedrock')
    echo 'Wordpress Bedrock deployment'
    # Import the environment config
    echo 'Linking .env file';
    cd {{ $releases_dir }}/{{ $release }};
    ln -nfs {{ $shared_dir }}/.env .env;
    chgrp -h www-data .env;

    echo 'Linking upload directory';
    rm -rf {{ $releases_dir }}/{{ $release }}/public_html/app/uploads;
    cd {{ $releases_dir }}/{{ $release }};
    ln -nfs {{ $shared_dir }}/uploads uploads;
@endtask

@task('laravel')
    echo 'Laravel app deployment'
    # Remove the storage directory and replace with persistent data
    echo 'Linking storage directory';
    rm -rf {{ $releases_dir }}/{{ $release }}/storage;
    cd {{ $releases_dir }}/{{ $release }};
    ln -nfs {{ $shared_dir }}/storage storage;

    # Import the environment config
    echo 'Linking .env file';
    cd {{ $releases_dir }}/{{ $release }};
    ln -nfs {{ $shared_dir }}/.env .env;
    chgrp -h www-data .env;

    php artisan cache:clear
    php artisan view:clear
    php artisan config:cache
    php artisan migrate --force
    php artisan queue:restart
@endtask
