################################################################################
# This script makes a preview archive of a travis build and sends it to a remote
# server. It must be executed from the root directory of the platform.
################################################################################

set -e

: ${TRAVIS_PULL_REQUEST:?"must be set"}
: ${REMOTE_HOST:?"must be set"}
: ${REMOTE_USER:?"must be set"}
: ${REMOTE_PASS:?"must be set"}
: ${PREVIEW_PATH:?"must be set"}

PREVIEW="pr-$TRAVIS_PULL_REQUEST-`date +%s`.tar.gz"

mysqldump --opt --no-create-db claroline_test -uroot --password="" > claroline.sql
rm -rf app/cache/* app/logs/* web/bundles
rm -rf web/data
tar --exclude=".git" -czf $PREVIEW *

export SSHPASS=$REMOTE_PASS

sshpass -e scp -q -o stricthostkeychecking=no $PREVIEW $REMOTE_USER@$REMOTE_HOST:$PREVIEW_PATH/$PREVIEW
