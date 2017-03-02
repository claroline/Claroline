################################################################################
# This script handles dependency fetching in travis builds. Whenever possible,
# it reuses dependency sets which where built by previous requests and sent to a
# remote cache server. When a set is not already cached, the usual commands
# (composer update, npm install, etc.) are performed and the result is
# compressed and sent to the remote server for later use.
#
# Note that:
#
# 1. this script must be executed with bash, not sh
# 2. the working directory must be the root directory of the platform
# 3. for internal pull requests, the following environment variables are all
#    supposed to be available:
#      - TRAVIS_REPO_SLUG (set by travis)
#      - REMOTE_HOST
#      - REMOTE_USER
#      - REMOTE_PASS
#      - CACHE_PATH
################################################################################

set -e
set -o pipefail

: ${TRAVIS_REPO_SLUG:?"must be set"}

DIST=../$TRAVIS_REPO_SLUG

# Each set of dependencies is identified by a checksum of the files that
# describe those dependencies (composer.json for composer, bower.json for bower,
# etc.). Any change in these files will lead to a cache miss.
COMPOSER_SUM=`cat composer.json $DIST/composer.json | md5sum | cut -c -32`
NPM_SUM=`cat package.json npm-shrinkwrap.json $DIST/package.json | md5sum | cut -c -32`
BOWER_SUM=`cat bower.json $DIST/bower.json | md5sum | cut -c -32`

# Fetches the dependencies managed by a given package manager. If a cache
# version is available, uses it, otherwise resolves the dependencies and sends
# them to the cache.
#
# $1 packager name (composer, bower, etc.)
# $2 dependencies checksum
# $3 update/install command
# $4 dependencies directory
fetch() {
    ARCHIVE="$1-$2.tar.gz"

    echo "Trying to fetch $1 dependencies from cache ($ARCHIVE)..."

    set +o errexit # allow curl failure
    STATUS=`curl -o $ARCHIVE -s -w "%{http_code}" "$REMOTE_HOST/cache/$ARCHIVE"`
    set -e

    if [ $STATUS = 200 ]
    then
        echo "Success, extracting..."
        tar -xzf "$ARCHIVE"
    else
        echo "Failure ($STATUS), executing $1..."
        eval $3

        if [ -z ${REMOTE_HOST+x} ]
        then
            # We need to access encrypted environment variables to push the
            # package through SSH, but those variables aren't available in PRs
            # built from fork repos, so that part must be skipped.
            #
            # see https://docs.travis-ci.com/user/pull-requests#Security-Restrictions-when-testing-Pull-Requests
            echo 'Not an internal PR, skipping caching part...'
        else
            echo "Compressing $4 directory ($ARCHIVE)..."
            rm -f $ARCHIVE
            tar --exclude=".git" -czf $ARCHIVE $4
            echo "Sending archive to remote cache..."
            export SSHPASS=$REMOTE_PASS
            sshpass -e scp -o stricthostkeychecking=no $ARCHIVE $REMOTE_USER@$REMOTE_HOST:$CACHE_PATH/$ARCHIVE
        fi
    fi

    rm -f $ARCHIVE
}

fetch composer $COMPOSER_SUM "composer update --prefer-dist" vendor

# we don't want the sources present in the cache
echo "Overriding distribution package with local build/repo..."
rm -rf vendor/claroline/distribution
cp -r $DIST vendor/claroline/distribution

# this is normally done in the post-update-cmd script
echo "Building app/config/bundles.ini..."
composer bundles

# Gets npm dependencies
# Removes the shrinkwrap to allow a PR to update dependencies
# After installation, a new shrinkwrap is generated
fetch npm $NPM_SUM "rm -f npm-shrinkwrap.json && npm install && npm shrinkwrap" node_modules

fetch bower $BOWER_SUM "npm run bower" web/packages
