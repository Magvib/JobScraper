<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/Magvib/JobScraper.git');
set('node_version', '23');

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
    run('apt install -y gh npm'); // TODO add fnm install 23 && fnm use 23
});
