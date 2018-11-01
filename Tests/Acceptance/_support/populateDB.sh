#!/bin/bash
DB_HOST="${typo3DatabaseHost}"
DB_PORT="${typo3DatabasePort:-3306}"
DB_USERNAME="${typo3DatabaseUsername}"
DB_DATABASE="${typo3DatabaseName}"
DB_PASSWORD="${typo3DatabasePassword}"

ARGS="-h ${DB_HOST} -P ${DB_PORT} --protocol=TCP -u ${DB_USERNAME}  -D ${DB_DATABASE}"
if [ -n "${DB_PASSWORD}" ]; then
    ARGS="$ARGS -p${DB_PASSWORD}"
fi

for db in `ls Tests/Acceptance/_data/sql/*.sql`; do
    echo "Import table: $db"
    echo "truncate table $(basename -s '.sql' $db)" | mysql $ARGS
    cat $db | mysql $ARGS
done
