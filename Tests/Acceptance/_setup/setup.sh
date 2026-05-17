#!/bin/bash
set -eu -o pipefail

[ -z "${TYPO3_PATH_ROOT:-}" ] && export TYPO3_PATH_ROOT="${PWD}/.Build/public"
[ -z "${TYPO3_PATH_APP:-}" ] && export TYPO3_PATH_APP="${PWD}"
[ -z "${typo3DatabaseDriver:-}" ] && export typo3DatabaseDriver="pdo_sqlite"
# Site base URL for TYPO3 setup; override for Docker (e.g. http://app:8888/)
[ -z "${TYPO3_SETUP_CREATE_SITE:-}" ] && export TYPO3_SETUP_CREATE_SITE="http://127.0.0.1:8888/"

CONSOLE_CMD=".Build/vendor/bin/typo3"

if [ ! -f "$CONSOLE_CMD" ]; then
  echo "TYPO3 Console not found. Please run composer install first."
  exit 1
fi

if [ "$typo3DatabaseDriver" == "pdo_sqlite" ]; then

    # Cleanup legacy config paths
    rm -f "${TYPO3_PATH_ROOT}/typo3conf/LocalConfiguration.php"
    rm -f "${TYPO3_PATH_APP}/config/system/settings.php"

    rm -f "${TYPO3_PATH_APP}/var/*.sqlite"
    rm -rf "${TYPO3_PATH_APP}/var/sqlite"
    rm -rf "${TYPO3_PATH_APP}/var/cache"

    # Install TYPO3 with SQLite (TYPO3 v13+ option names)
    set -x
    $CONSOLE_CMD setup \
      --driver sqlite \
      --admin-username test \
      --admin-user-password "Test1234%" \
      --project-name "testsite" \
      --create-site "${TYPO3_SETUP_CREATE_SITE}" \
      --server-type other \
      --no-interaction --force

     dbfile="$($CONSOLE_CMD configuration:show DB/Connections/Default/path  | sed -n 2p | tr ',' ' ' | xargs)"

     echo $dbfile

     if [ -f "$dbfile" ]; then
        ln -sf "$dbfile" "$(dirname $dbfile)/current.sqlite"
        # Enable WAL mode for concurrent access (prevents lock contention between Codeception Db module and CLI commands)
        echo "PRAGMA journal_mode=WAL;" | sqlite3 $dbfile
        # populate the database
        for db in `ls ./Tests/Acceptance/_data/sql/*.sql`; do
            echo "Import table: $db"
            echo "DELETE FROM $(basename -s '.sql' $db);" | sqlite3 $dbfile
            cat $db | sqlite3 $dbfile
        done
        echo "VACUUM;" | sqlite3 $dbfile
     fi

    # Create all cache and schema tables so TYPO3 doesn't try to create them lazily at runtime
    $CONSOLE_CMD database:updateschema --no-interaction
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

    # Install TYPO3 with MySQL (TYPO3 v13+ option names)
    $CONSOLE_CMD setup \
            --username=$typo3DatabaseUsername \
            --password=$typo3DatabasePassword \
            --host=$typo3DatabaseHost \
            --port=${typo3DatabasePort:-3306} \
            --dbname=$DBNAME \
            --admin-username=test \
            --admin-user-password=test1234 \
            --project-name="testsite" \
            --no-interaction --force


    # populate the database
    for db in `ls ./Tests/Acceptance/_data/sql/*.sql`; do
        echo "Import table: $db"
        echo "truncate table $(basename -s '.sql' $db)" | mysql -D $DBNAME $ARGS
        cat $db | mysql -D $DBNAME $ARGS
    done
fi


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
 $CONSOLE_CMD configuration:set SYS/trustedHostsPattern '.*'
 $CONSOLE_CMD configuration:set SYS/displayErrors '1'
 $CONSOLE_CMD configuration:set SYS/devIPmask '*'
 $CONSOLE_CMD configuration:set FE/pageNotFoundOnCHashError 0
