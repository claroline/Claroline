#!/bin/bash

BASE_VERSION=$1
BRANCH_NAME=$2
TYPE_VERSION=$3

# compare last commit of distribution repo
if [ -f 'VERSION.txt' ]; then
   LAST_COMMIT=`cat VERSION.txt | sed -n 2p`
   LAST_VERSION_BLOCK=`cat VERSION.txt | sed -n 1p | cut -d "." -f3`
else
   LAST_COMMIT='I used to be an adventurer just like you, but then I took an arrow in the knee'
   if [ ! -z "${TYPE_VERSION}" ]; then
     LAST_VERSION_BLOCK="0-${TYPE_VERSION}0"
   else
     LAST_VERSION_BLOCK=0
   fi
fi

echo "Last minor version: ${LAST_VERSION_BLOCK}"

CURRENT_COMMIT=`git rev-parse HEAD`

if [ "${CURRENT_COMMIT}" = "${LAST_COMMIT}" ]; then
  echo "Nothing to update, already at the latest version"
  exit 0
fi

LAST_MINOR_VERSION=`echo "${LAST_VERSION_BLOCK}" | cut -d "-" -f1`

if [ ! -z "${TYPE_VERSION}" ]; then

  LAST_RELEASE_VERSION=`echo "${LAST_VERSION_BLOCK}" | grep -oP "(?<=${TYPE_VERSION}).*"`
  LAST_RELEASE_VERSION=$((LAST_RELEASE_VERSION + 1))
  CURRENT_VERSION_BLOCK="${LAST_MINOR_VERSION}-${TYPE_VERSION}${LAST_RELEASE_VERSION}"
else
  CURRENT_VERSION_BLOCK=$((LAST_MINOR_VERSION + 1))
fi

FULL_VERSION="${BASE_VERSION}.${CURRENT_VERSION_BLOCK}"

echo "Current minor version: ${FULL_VERSION}"

# create the VERSION.txt file
{
echo $FULL_VERSION
echo `git rev-parse HEAD`
echo $BRANCH_NAME
} > VERSION.txt

# building log file
LOGS=`git log ${LAST_COMMIT}..${CURRENT_COMMIT} --oneline`

COMMITS="$( cut -d " " -f -1 <<< "$LOGS" )"
COMMITNAMES="$( cut -d " " -f 2- <<< "$LOGS" )"

COMMITS=$(tr " " "\n" <<< "$COMMITS")
mapfile -t COMMITNAMES <<< "$COMMITNAMES"

COMMITSTRING=''
MERGESTRING=''

i=0

 printf "# Version $FULL_VERSION  "$'\n'$'\n' >> changelogs/${BRANCH_NAME}-${BASE_VERSION}.x.md

for COMMIT in $COMMITS
do
    if [[ ${COMMITNAMES[$i]} == *"Merge"* ]]; then
        #we don't log them yet, but jenkins already do that
        MERGESTRING="${MERGESTRING}\nclaroline/distribution@${COMMIT} - ${COMMITNAMES[$i]}"
    else
        NAMELINKS=`echo ${COMMITNAMES[$i]} | sed -r 's/\(#([^)]*)\).*/[#\1]\(https:\/\/github.com\/claroline\/Distribution\/pull\/\1\)/'`
        printf "[${COMMIT}](https://github.com/claroline/Distribution/commit/${COMMIT}) - ${NAMELINKS}  "$'\n' >> changelogs/${BRANCH_NAME}-${BASE_VERSION}.x.md
    fi
    i=$((i + 1))
done

 printf $'\n' >> changelogs/${BRANCH_NAME}-${BASE_VERSION}.x.md
