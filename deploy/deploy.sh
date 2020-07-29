#!/usr/bin/env bash

#  1. Clone complete SVN repository to separate directory
printf "1. Cloning source repository...\n"
svn co $SVN_REPOSITORY ../svn

#  2. Copy git repository contents to SNV trunk/ directory
printf "2. Copy git source to svn...\n"
cp -R ./* ../svn/trunk/

#  3. Go to trunk/
printf "3. Go to svn repository...\n"
cd ../svn/trunk/

#  4. Move screenshots/ to SVN /assets/
printf "4. Copy assets...\n"
mv ./screenshots/ ../assets/

#  5. Cleanup repository
printf "5. Clean up files...\n"
#  5.1 Delete .git/
rm -rf .git/
#  5.2 Delete deploy/
rm -rf deploy/
#  5.3 Delete .travis.yml
rm -rf .travis.yml
#  5.4 Delete README.md
rm -rf README.md


#  5.1 Confirm all files to be versioned
printf "5.1. Go to svn root directory...\n"
svn add --force .

#  6. Go to SVN home directory
printf "6. Go to svn root directory...\n"
cd ../

#. 7. Check for semver tag
printf "7. Detect deployment type...\n"
semver_pattern="^[0-9]+\.[0-9]+.[0-9]+\.[0-9]+$"
if [[ $TRAVIS_TAG =~ $semver_pattern ]]; then
  #  8. Publish new release
  printf "8. Publish new release...\n"
  #  8.1 Copy trunk/ to tags/{tag}/
  svn cp trunk tags/$TRAVIS_TAG
  #  8.2 Remove readme.txt from plugin archive
  #  svn remove --force tags/$TRAVIS_TAG/readme.txt

  #  8.3 Set commit message
  SVN_COMMIT_MESSAGE="Release $TRAVIS_TAG"
else
  #  8. Just update trunk
  printf "8. Just update trunk...\n"
  #  8.2 Set commit message
  SVN_COMMIT_MESSAGE="Revise $TRAVIS_TAG"
fi

#  9. Commit SVN tag
printf "9. Commit changes '$SVN_COMMIT_MESSAGE'...\n"
svn ci  --message "$SVN_COMMIT_MESSAGE" \
        --username $SVN_USERNAME \
        --password $SVN_PASSWORD \
        --non-interactive \
        --trust-server-cert