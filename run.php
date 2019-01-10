<?php // 7.2
$loader = require __DIR__ . '/vendor/autoload.php';

use Ramsey\Uuid\Uuid;

define('BASH_PATTERN', '/^([A-Za-z\_]+?[A-Za-z0-9\_]*?)$/');
define('RTSZ_TEMPLATE', 'TMPL.rtsz');

function printHelp() {
    die('program [bash var prefix] <name>');
}

$smarty = new Smarty();
$smarty->setCompileDir(__DIR__.DIRECTORY_SEPARATOR."templates_c");
$smarty->setTemplateDir(__DIR__.DIRECTORY_SEPARATOR."templates");

$bashPrefix = '';
$projectNamePrefix = '';

if ($argc !== 2 && $argc !== 3) {
    printHelp();
}

switch ($argc) {
    case 2:
        $trimmed1 = trim($argv[1]);
        if (!preg_match(BASH_PATTERN, $trimmed1)) {
            die('Bad name provided, cannot be used in bash variable names.');
        }

        $bashPrefix = strtoupper($trimmed1);
        $projectNamePrefix = $trimmed1;
        break;
    case 3:
        $trimmed1 = trim($argv[1]);
        $trimmed2 = trim($argv[2]);
        if (!preg_match(BASH_PATTERN, $trimmed1)) {
            die('Bad bash script name provided.');
        }

        $bashPrefix = strtoupper($trimmed1);
        $projectNamePrefix = $trimmed2;
        break;
    default: printHelp();
}

/**
 * Get the Extension of a File
 * @param $file file's name
 * @return string|bool the extracted extension of the file or false on failure.
 */
function getExt($file) {
    return substr($file, strrpos($file, ".") + 1);
}

define('UUID_CACHE', __FILE__.':uuids');

$GLOBALS[UUID_CACHE] = [];

function gen_uuid($params, $smarty) {

    if (empty($params["old"])) {
        return "";
    } else {
        $old = $params['old'];

        if ($old == '00000000-0000-0000-0000-000000000000') {
            return $old;
        } else {
            if (array_key_exists($old, $GLOBALS[UUID_CACHE])) {
                return $GLOBALS[UUID_CACHE][$old];
            } else {
                $uuid4 = Uuid::uuid4();
                $GLOBALS[UUID_CACHE][$old] = $uuid4->toString();
                return $GLOBALS[UUID_CACHE][$old];
            }
        }
    }
}

$files = [RTSZ_TEMPLATE, 'shell-env.sh'];
$delims = ['*'    => ['{',      '}'],
           'rtsz' => ['`!',     '!`'],
           'sh'   => ['TMPL(', ')']];

$outTemplate = $projectNamePrefix.'.rtsz';

$smarty->assign('envVarPrefix', $bashPrefix);
$smarty->assign('templateOutPath', realpath($outTemplate));
$smarty->assign('invokerName', posix_getpwuid(posix_geteuid())['name']);
$smarty->assign('name', $projectNamePrefix);
$smarty->assign('timeStamp', date('m/d/Y H:i:s.v'));

$smarty->registerPlugin("function", 'uuid', 'gen_uuid');

foreach ($files as $file) {
    $out = $file === RTSZ_TEMPLATE ? $outTemplate : $file;
    $smarty->escape_html = $file === RTSZ_TEMPLATE;

    $delimSet = $delims[array_key_exists(getExt($file), $delims) ? getExt($file) : '*'];

    $smarty->left_delimiter = $delimSet[0];
    $smarty->right_delimiter = $delimSet[1];


    file_put_contents($out, $smarty->fetch($file));
}
?>