#!/bin/bash

REPOSITORY=$1
BRANCH=$2
WORKINGDIR=$3
USER=$4

su - $USER

whoami

if [ -z "$REPOSITORY" ] || [ -z "$BRANCH" ] || [ -z "$WORKINGDIR" ]; then
  echo 'Error, params missing!'
  exit
fi

echo $REPOSITORY
echo $BRANCH
echo $WORKINGDIR

# move to the working dir
cd $WORKINGDIR || exit