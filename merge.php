<?php

$vendors = [
  'claroline' => [
    'ActivityToolBundle',
    'AnnouncementBundle',
    'BundleRecorder',
    'CoreBundle',
    'CursusBundle',
    'ForumBundle',
    'ImagePlayerBundle',
    'InstallationBundle',
    'KernelBundle',
    'MessageBundle',
    'MigrationBundle',
    'PdfPlayerBundle',
    'RssReaderBundle',
    'ScormBundle',
    'SurveyBundle',
    'TeamBundle',
    'VideoPlayerBundle',
    'WebInstaller',
    'WebResourceBundle'
  ],
  'formalibre' => [
    'PresenceBundle',
    'ReservationBundle',
    'SupportBundle'
  ],
  'hevinci' => [
    'CompetencyBundle',
    'FavouriteBundle',
    'UrlBundle'
  ],
  'icaplyon1' => [
    'BadgeBundle',
    'BlogBundle',
    'DropzoneBundle',
    'LessonBundle',
    'PortfolioBundle',
    'SocialMediaBundle',
    'WebsiteBundle',
    'WikiBundle',
    'OauthBundle'
  ],
  'innovalangues' => [
    'PathBundle'
  ],
  'ujm-dev' => [
    'ExoBundle'
  ]
];

$remotes = array_map(function ($line) {
  $parts = explode("\t", $line);
  $remote = array_shift($parts);

  return $remote;
}, cmd('git remote -v'));

foreach ($vendors as $vendor => $packages) {
  foreach ($packages as $package) {
    $isNew = !in_array($package, $remotes);
    $prefixCommits = true;

    if ($isNew) {
      cmd("git remote add {$package} http://github.com/{$vendor}/{$package}");
      cmd("git fetch --no-tags {$package} master");
      cmd("git branch -f {$package} {$package}/master");
      cmd("git checkout {$package}");
      $rewriteRange = 'HEAD';
    } else {
      cmd("git checkout {$package}");
      $previousRevision = cmd("git rev-parse HEAD")[0];
      cmd("git fetch --no-tags {$package} master");
      $rewriteRange = "{$previousRevision}..HEAD";
      $list = cmd("git rev-list {$rewriteRange}");
      $prefixCommits = count($list) > 0;
    }

    cmd("git pull --no-tags --no-commit {$package} master");

    if ($prefixCommits) {
      cmd("git filter-branch -f --msg-filter 'sed \"1 s/^/[{$package}] /\"' {$rewriteRange}");
    }

    cmd("git checkout master");

    if ($isNew) {
      cmd("git read-tree --prefix={$package}/ -u {$package}");
      cmd("git commit -m 'Add {$package} package'");
    }

    cmd("git merge -s subtree {$package}");
  }
}

function cmd($cmd) {
  echo "Executing: {$cmd}\n";
  exec($cmd, $output, $code);

  if ($code !== 0) {
    die("Command failed, aborting\n");
  }

  return $output;
}
