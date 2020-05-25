#!/bin/bash
set -ev

[ -z "$TYPO3_PATH_ROOT" ] && TYPO3_PATH_ROOT=$PWD/.Build/public

if [ "$typo3DatabaseDriver" == "pdo_sqlite" ]; then
    # sqlite DB
    # install typo3
    rm -f "${TYPO3_PATH_ROOT}/typo3conf/LocalConfiguration.php"
    rm -f "${TYPO3_PATH_ROOT}/../../../var/*.sqlite"
    ./.Build/vendor/bin/typo3cms -vvv install:setup --database-driver pdo_sqlite \
            --admin-user-name test --admin-password test1234 \
            --site-name "testsite" --site-setup-type site --no-interaction --force

     dbfile="$(./.Build/vendor/bin/typo3cms configuration:show DB/Connections/Default/path  | sed -n 2p | tr ',' ' ' | xargs)"

     if [ -f "$dbfile" ]; then
        ln -sf "$dbfile" "$(dirname $dbfile)/current.sqlite"
        # populate the database
        for db in `ls ./Tests/Acceptance/_data/sql/*.sql`; do
            echo "Import table: $db"
            echo "DELETE FROM $(basename -s '.sql' $db)" | sqlite3 $dbfile
            cat $db | sqlite3 $dbfile
        done
        echo "VACUUM" | sqlite3 $dbfile
     fi
else
    # mysql db
    if [ -z "$typo3DatabaseUsername" ] || [ -z "$typo3DatabaseHost" ] || [ -z "$typo3DatabaseName" ]; then
        echo "No database configuration."
        exit 1
    fi

    DBNAME="${typo3DatabaseName}_acceptancetest"
    ARGS="-u $typo3DatabaseUsername -h $typo3DatabaseHost -P ${typo3DatabasePort:-3306}"

    if [ -n "${typo3DatabasePassword}" ]; then
        ARGS="$ARGS -p${typo3DatabasePassword}"
    fi

    # delete database
    mysql $ARGS -e """
        DROP DATABASE IF EXISTS "${DBNAME}";
        CREATE DATABASE "${DBNAME}" DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
    """

    # install typo3
    ./.Build/vendor/bin/typo3cms -vvv install:setup --database-user-name=$typo3DatabaseUsername --database-user-password=$typo3DatabasePassword \
            --database-host-name=$typo3DatabaseHost --database-port=${typo3DatabasePort:-3306} --database-name=$DBNAME \
            --use-existing-database --admin-user-name=test --admin-password=test1234 \
            --site-name="testsite" --site-setup-type=no --no-interaction --force


    # populate the database
    for db in `ls ./Tests/Acceptance/_data/sql/*.sql`; do
        echo "Import table: $db"
        echo "truncate table $(basename -s '.sql' $db)" | mysql -D $DBNAME $ARGS
        cat $db | mysql -D $DBNAME $ARGS
    done
fi

./.Build/vendor/bin/typo3cms install:generatepackagestates
# symlink fileadmin/user_upload to fixtures folder
(
    cd .Build/public/fileadmin/user_upload;
    test -L nightlife-4.jpg || {
        ln -s ../../../../Tests/Functional/Fixtures/fileadmin/user_upload/nightlife-4.jpg .
    }
)

# TYPO3_CONF_VARS
 ./.Build/vendor/bin/typo3cms configuration:set SYS/trustedHostsPattern '.*'
 ./.Build/vendor/bin/typo3cms configuration:set SYS/displayErrors '1'
 ./.Build/vendor/bin/typo3cms configuration:set SYS/devIPmask '*'
