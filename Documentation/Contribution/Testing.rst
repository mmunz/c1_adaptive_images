.. include:: ../Includes.txt

.. _testing:

Tests
-----
For tests you first need to prepare a local typo3 instance for testing inside this extensions root folder using composer.

You can test different TYPO3 versions:

* for 9.5.x: ``composer require typo3/minimal=^9.5 && git checkout composer.json``
* for 8.7.x: ``composer require typo3/minimal=^8.7 && git checkout composer.json``

**Unit** tests should just work OOTB with: ``composer tests:unit``

**Functional and acceptance tests** require a working mysql database where the testuser is allowed to create tables.
Also some environment variables are required:

.. code-block:: bash

  export typo3DatabaseName=testing
  export typo3DatabaseUsername=testing
  export typo3DatabasePassword=testing
  export typo3DatabaseHost=127.0.0.1
  export TYPO3_PATH_ROOT=$PWD/.Build/public/