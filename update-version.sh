MAJOR_VERSION=$1
BRANCH_NAME=$2

# compare last commit of distribution repo
if [ -f 'VERSION.txt' ]; then
   LAST_COMMIT=`cat VERSION.txt | sed -n 2p`
   LAST_MINOR_VERSION=`cat VERSION.txt | sed -n 1p | cut -d "." -f2`
else
   LAST_COMMIT='I used to be an adventurer just like you, but then I took an arrow in the knee'
   LAST_MINOR_VERSION=0
fi 

CURRENT_COMMIT=`git rev-parse HEAD`
CURRENT_MINOR_VERSION=$((LAST_MINOR_VERSION + 1))
FULL_VERSION="${MAJOR_VERSION}.${CURRENT_MINOR_VERSION}"

if [ "${CURRENT_COMMIT}" = "${LAST_COMMIT}" ]; then
  echo "Nothing to update, already at the latest version"
  exit 0
fi

# create the VERSION.txt file
{              
echo $FULL_VERSION
echo `git rev-parse HEAD`
echo $BRANCH_NAME
} > VERSION.txt
