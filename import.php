<?php

if ($argc < 4) {
  die(
    "Usage:    php import.php <account> <namespace> <bundle>\n" .
    "Example:  php import.php UJM-dev UJM ExoBundle\n"
  );
}

$account = $argv[1];
$namespace = $argv[2];
$bundle = $argv[3];
$prefix = $argv[4];

// Import the target repository in a dedicated branch and prefix
// all of its commits with the name of the bundle

cmd("git remote add {$bundle} http://github.com/{$account}/{$bundle}");
cmd("git fetch --no-tags {$bundle} master");
cmd("git branch -f {$bundle} {$bundle}/master");
cmd("git checkout {$bundle}");
cmd("git pull --no-tags {$bundle} master");
cmd("git filter-branch -f --msg-filter 'sed \"1 s/^/[{$bundle}] /\"' HEAD");

// Replace the namespace of the bundle by "Claroline" if needed
/*
if ($namespace !== 'Claroline') {
  $iterator = new RecursiveDirectoryIterator(__DIR__);
  $iteratorIterator = new RecursiveIteratorIterator($iterator);
  $sources = new RegexIterator(
    $iteratorIterator,
    '/^.+\.(php|yaml|yml|xml|twig|twigjs)$/i',
    RecursiveRegexIterator::GET_MATCH
  );

  foreach ($sources as $file) {
    cmd("sed -i -e 's/{$namespace}/Claroline/' {$file[0]}");
  }

  cmd("git add .");
  cmd("git commit -m '[$bundle] Switch to Claroline namespace'");
}
*/
// Merge the branch into master using subtree feature

cmd("git checkout import-with-history");
cmd("git read-tree --prefix={$prefix}/ -u {$bundle}");
cmd("git commit -m 'Import {$bundle}'");
cmd("git merge -s subtree {$bundle}");

function cmd($cmd) {
  echo "Executing: {$cmd}\n";
  exec($cmd, $output, $code);

  if ($code !== 0) {
    die("Command failed, aborting\n");
  }

  return $output;
}
