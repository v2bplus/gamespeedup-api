<?php

use Symfony\Component\Yaml\Yaml;

defined('DS') || define('DS', '/');
define('BASE_PATH', realpath(dirname(__FILE__).DS.'..'.DS.'..').DS);
define('PUBLIC_PATH', BASE_PATH.'public'.DS);
define('APPLICATION_PATH', BASE_PATH.'application'.DS);
define('BIN_PATH', APPLICATION_PATH.'bin'.DS);

function out($out)
{
    if (is_string($out)) {
        echo $out."\n";
    }
    if (is_array($out)) {
        foreach ($out as $line) {
            out($line);
        }
    }
}

function parseVersion($stdout)
{
    preg_match('/\\d+(?:\\.\\d+)+/', $stdout, $matches);
    if (!is_null($matches) && isset($matches[0])) {
        return $matches[0];
    }

    return null;
}
function runProcess($cmd, $input = null)
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open(
        $cmd,
        $descriptorSpec,
        $pipes
    );
    if (!is_resource($process)) {
        return 'ERROR - Could not start subprocess.';
    }
    $output = $error = '';
    fwrite($pipes[0], $input);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    proc_close($process);
    if (strlen($error)) {
        return 'ERROR - '.$error;
    }

    return $output;
}
// check php version
$version = phpversion();
if (version_compare($version, '8.2.0', '<')) {
    out('Php need update.');
    exit(9);
}
// check php extension
$extension = ['yaf', 'redis', 'mysqli'];
foreach ($extension as $key => $value) {
    if (!extension_loaded($value)) {
        out('Php need extension '.$value);
        exit(9);
    }
}

//  check composer
$composerVersion = shell_exec('composer --version');
if (version_compare(parseVersion($composerVersion), '2.0.0', '<')) {
    out('Composer need update.');
    exit(9);
}

out('Installing dependencies.');
$cmd = 'composer install --prefer-dist --no-plugins';
$output = runProcess($cmd);
out($output);

$projectPath = realpath(dirname(__FILE__).DS);

$cachePath = APPLICATION_PATH.'/cache';
if (!is_dir($cachePath)) {
    mkdir($cachePath, 777);
}
$logsPath = APPLICATION_PATH.'/logs';
if (!is_dir($logsPath)) {
    mkdir($logsPath, 777);
}
$uploadPath = PUBLIC_PATH.'/upload';
if (!is_dir($uploadPath)) {
    mkdir($uploadPath, 755);
}
$dataPath = APPLICATION_PATH.'/data';
if (!is_dir($dataPath)) {
    mkdir($dataPath, 755);
}
$currentPerms = substr(decoct(fileperms($cachePath)), 2);
if (755 != $currentPerms) {
    chmod($cachePath, 755);
}
$currentPerms = substr(decoct(fileperms($logsPath)), 2);
if (755 != $currentPerms) {
    chmod($logsPath, 755);
}
$currentPerms = substr(decoct(fileperms($dataPath)), 2);
if (755 != $currentPerms) {
    chmod($dataPath, 755);
}

$resqueConfigPath = APPLICATION_PATH.'/conf/resque.yml';
if (file_exists($resqueConfigPath)) {
    $currentPerms = substr(decoct(fileperms($resqueConfigPath)), 2);
    if (755 != $currentPerms) {
        chmod($resqueConfigPath, 755);
    }
}

$phinxConfigPath = APPLICATION_PATH.'/conf/phinx.yml';
if (file_exists($phinxConfigPath)) {
    $currentPerms = substr(decoct(fileperms($phinxConfigPath)), 2);
    if (755 != $currentPerms) {
        chmod($phinxConfigPath, 755);
    }
}

$app = new Yaf_Application(APPLICATION_PATH.'conf/application.ini');
$app->bootstrap();
$config = Yaf_Application::app()->getConfig()->toArray();
$mysqlConfig = $config['mysql'];

$phinxConfigFile = APPLICATION_PATH.'conf'.DS.'phinx.yml';
$writeConfig = $environments = $production = $paths = [];

$production['adapter'] = $mysqlConfig['database_type'];
$production['host'] = $mysqlConfig['server'];
$production['port'] = (int) $mysqlConfig['port'];
$production['name'] = $mysqlConfig['database_name'];
$production['user'] = $mysqlConfig['username'];
$production['pass'] = $mysqlConfig['password'];
$production['charset'] = $mysqlConfig['charset'];
$production['collation'] = $mysqlConfig['collation'];

$environments['production'] = $production;
$environments['default_environment'] = 'production';
$environments['default_migration_table'] = 'phinxlog';

$paths['migrations'] = APPLICATION_PATH.'migrations';
$writeConfig['environments'] = $environments;
$writeConfig['paths'] = $paths;

$yaml = Yaml::dump($writeConfig, 3);
file_put_contents($phinxConfigFile, $yaml);

$phinxPath = BIN_PATH.'phinx.php';
${$cmd} = '/usr/bin/env php '.$phinxPath.' migrate --configuration='.$phinxConfigFile.' --environment=production';
$rs = shell_exec($cmd);
print_r($rs);
