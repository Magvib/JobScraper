<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/Magvib/JobScraper.git');
set('node_version', '23');
set('http_user', 'www-data');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('jobscraper.work')
    ->set('remote_user', 'deployer')
    ->set('hostname', '46.225.146.106')
    ->set('deploy_path', '~/JobScraper');

// Hooks

after('deploy:failed', 'deploy:unlock');

after('deploy:update_code', function () {
    run('cd {{release_path}} && npm install && npm run build');
});

before('provision:update', function () {
    run('apt install -y gh npm');
});

after('provision:node', function () {
    run('fnm install 23');
    run('fnm use 23');
    run('npm install pm2@latest -g');
});

after('deploy:symlink', function () {
    $alias = currentHost()->get('alias') ?? 'default';
    $pm2Process = 'queue-worker' . '-' . md5($alias);
    $cronProcess = 'scheduler' . '-' . md5($alias);

    // Check if PM2 process exists
    $exists = run("pm2 list | grep {$pm2Process} || true");

    if (empty($exists)) {
        writeln("PM2 process not found. Starting new queue worker...");

        run("cd {{deploy_path}}/current && pm2 start artisan --name {$pm2Process} --interpreter php -- queue:work --quiet");
    } else {
        writeln("PM2 process exists. Restarting queue worker...");

        run("pm2 restart {$pm2Process}");
    }

    // Save PM2 process list so it restarts on reboot
    run("pm2 save");

    $cronLine = "* * * * * cd {{deploy_path}}/current && php artisan schedule:run >> /dev/null 2>&1 # $cronProcess";

    // Check if this specific scheduler already exists
    $cronExists = run("crontab -l | grep '$cronProcess' || true");

    if (empty($cronExists)) {
        writeln("Adding Laravel scheduler to crontab for $cronProcess...");

        run("(crontab -l 2>/dev/null; echo \"$cronLine\") | crontab -");
    } else {
        writeln("Laravel scheduler already exists for $cronProcess.");
    }
});

