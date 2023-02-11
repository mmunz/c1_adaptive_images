#!/bin/bash
set -ev

[ -z "$TYPO3_PATH_ROOT" ] && TYPO3_PATH_ROOT=$PWD/.Build/public

if [ "$typo3DatabaseDriver" == "pdo_sqlite" ]; then


    # Cleanup Up to TYPO3 v11
    rm -f "${TYPO3_PATH_ROOT}/typo3conf/LocalConfiguration.php"
    # Cleanup TYPO3 v12
    rm -f "${TYPO3_PATH_ROOT}/../config/system/settings.php"

    rm -f "${TYPO3_PATH_ROOT}/../../../var/*.sqlite"

    # install typo3 with sqlite DB

    # TYPO3_PATH_APP=$PWD/.Build/ \
    TYPO3_DB_DRIVER=sqlite \
    TYPO3_DB_USERNAME=db \
    TYPO3_DB_PASSWORD=db \
    TYPO3_DB_PORT=3306 \
    TYPO3_DB_HOST=db \
    TYPO3_DB_DBNAME=db \
    TYPO3_SETUP_ADMIN_EMAIL=admin@email.com \
    TYPO3_SETUP_ADMIN_USERNAME=test \
    TYPO3_SETUP_PASSWORD=test1234 \
    TYPO3_PROJECT_NAME="Automated Setup" \
    TYPO3_CREATE_SITE="http://test.site/" \
    TYPO3_SETUP_CREATE_SITE="http://test.site/" \
    ./.Build/vendor/bin/typo3 setup --force --no-interaction

#    ./.Build/vendor/bin/typo3cms -vvv install:setup --database-driver pdo_sqlite \
#            --admin-user-name test --admin-password test1234 \
#            --site-name "testsite" --site-setup-type site --no-interaction --force

     dbfile="$(./.Build/vendor/bin/typo3cms configuration:show DB/Connections/Default/path  | sed -n 2p | tr ',' ' ' | xargs)"

     if [ -f "$dbfile" ]; then
        ln -sf "$dbfile" "$(dirname $dbfile)/current.sqlite"
        # populate the database
        for db in `ls ./Tests/Acceptance/_data/sql/*.sql`; do
            echo "Import table: $db"
            echo "DELETE FROM $(basename -s '.sql' $db);" | sqlite3 $dbfile
            cat $db | sqlite3 $dbfile
        done
        echo "VACUUM;" | sqlite3 $dbfile
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

# may fail because it is not needed anymore in TYPO3 11
./.Build/vendor/bin/typo3cms install:generatepackagestates || true
# symlink fileadmin/user_upload to fixtures folder
(
    test -d .Build/public/fileadmin/user_upload || mkdir -p .Build/public/fileadmin/user_upload
    cd .Build/public/fileadmin/user_upload;
    test -L nightlife-4.jpg || {
        ln -s ../../../../Tests/Fixtures/fileadmin/user_upload/nightlife-4.jpg .
    }
    test -L image-empty.jpg || {
        ln -s ../../../../Tests/Fixtures/fileadmin/user_upload/image-empty.jpg .
    }
)

# TYPO3_CONF_VARS
 ./.Build/vendor/bin/typo3cms configuration:set SYS/trustedHostsPattern '.*'
 ./.Build/vendor/bin/typo3cms configuration:set SYS/displayErrors '1'
 ./.Build/vendor/bin/typo3cms configuration:set SYS/devIPmask '*'
