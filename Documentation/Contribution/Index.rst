.. include:: ../Includes.txt

.. _contribution:

Contribute
==========

Target group: **Developers**

If you want contribute to this extension you're welcome to do so.

Here are some hints to get you started.

Code
----
Please use the repository at github (https://github.com/mmunz/c1_adaptive_images) for issues and pull requests.

Linting and code checks
-----------------------

Please make sure your code passes all linting and code checks before opening an pull request.

To run all checks:

..  code-block:: shell

    composer ci:static

This will lint PHP with php-cs-fixer, run PHPStan and typoscript-linter.

php-cs-fixer
^^^^^^^^^^^^

Lint PHP using

..  code-block:: shell

    composer php:lint

To automatically fix the PHP code:

..  code-block:: shell

    composer php:fix


Execute the tests
-----------------

The are some unit and acceptance tests in the ``Tests`` folder.

Please run the tests (see :ref:`testing`) and make sure they all pass before committing or opening a pull request.


Help with the documentation
---------------------------

If you want to make the documentation better:

* checkout the repository
* make sure you have docker installed and running, because the documentation is build using a docker container. See
  https://docs.typo3.org/typo3cms/HowToDocument/RenderingDocs/Quickstart.html#rendering-docs-quickstart for explanation
  and help.
* make your changes to the rst files
* rebuild documentation with ``composer build:doc``
* commit/open merge request
