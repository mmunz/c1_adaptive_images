.. include:: ../Includes.txt

.. _testing:

Tests
-----
For tests you first need to prepare a local typo3 instance for testing inside this extensions root folder using composer.

You can install different TYPO3 versions for testing. From inside the extension folder (typo3conf/ext/c1_adaptive_images):

* for 11.5.x: ``composer require typo3/minimal=^11.5 && git checkout composer.json``
* for 12.x.y: ``composer require typo3/minimal=^12 && git checkout composer.json``

**Unit** tests should just work OOTB with: ``composer tests:unit``

**Functional and acceptance tests** require a working mysql database where the testuser is allowed to create tables.
Also some environment variables are required (adapt to your database credentials):

.. code-block:: bash

  # export typo3DatabaseName=testing
  # export typo3DatabaseUsername=testing
  # export typo3DatabasePassword=testing
  # export typo3DatabaseHost=127.0.0.1
  export typo3DatabaseDriver=pdo_sqlite
  export TYPO3_PATH_ROOT=$PWD/.Build/public

For acceptance tests
====================

you also need to start a local webserver serving from the test-instance. This can be done with
the builtin webserver in php:

.. code-block:: bash

  cd typo3conf/ext/c1_adaptive_images
  php -S 127.0.0.1:8888 -t .Build/public/

Also selenium or chromedriver is required.

Start chromedriver with:

.. code-block:: bash

  chromedriver --url-base=wd/hub
