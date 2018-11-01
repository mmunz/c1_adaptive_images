#!/bin/bash

set -ev

if [ -z "$typo3DatabaseUsername" ] || [ -z "$typo3DatabaseHost" ] || [ -z "$typo3DatabaseName" ]; then
    echo "No database configuration."
    exit 1
fi

if [ -z "$TYPO3_PATH_ROOT" ]; then
    echo "TYPO3_PATH_ROOT is not set."
    exit 1
fi

ARGS="-u $typo3DatabaseUsername -h $typo3DatabaseHost -P ${typo3DatabasePort:-3306}"

if [ -n "${typo3DatabasePassword}" ]; then
    ARGS="$ARGS -p${typo3DatabasePassword}"
fi

mysql $ARGS -e """
    DROP DATABASE IF EXISTS ${typo3DatabaseName};
    CREATE DATABASE ${typo3DatabaseName} DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
"""


./.Build/vendor/bin/typo3cms -vvv install:setup --database-user-name=$typo3DatabaseUsername --database-user-password=$typo3DatabasePassword \
        --database-host-name=$typo3DatabaseHost --database-port=${typo3DatabasePort:-3306} --database-name=$typo3DatabaseName \
        --use-existing-database --admin-user-name=test --admin-password=test1234 \
        --site-name="testsite" --site-setup-type=none --no-interaction --force

# symlink fileadmin/user_upload to fixtures folder
(
    cd .Build/public/fileadmin/user_upload;
    test -L nightlife-4.jpg || {
        ln -s ../../../../Tests/Functional/Fixtures/fileadmin/user_upload/nightlife-4.jpg .
    }
)

 ./.Build/vendor/bin/typo3cms configuration:set SYS/trustedHostsPattern '.*'
 ./.Build/vendor/bin/typo3cms configuration:set SYS/displayErrors '1'
 ./.Build/vendor/bin/typo3cms configuration:set SYS/devIPmask '1'
