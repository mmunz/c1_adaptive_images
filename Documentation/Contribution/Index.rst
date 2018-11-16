.. include:: ../Includes.txt


.. _contribution:

Contribute
==========

Target group: **Developers**

If you can cantribute to this extension please do so.

Code
----
Please use the repository at github (https://github.com/mmunz/c1_adaptive_images) for issues and pull requests.

* make sure your code passes phpcs-fixer linting by running ``composer php:cs:lint`` or/and ``composer:php:fix`` scripts
* Run tests (see :ref:`testing`) and make sure they all pass before commiting or opening a pull request.


Documentation
-------------

If you want to make the documentation better:

* checkout the repository
* make sure you have docker installed and running, because the documentation is build using a docker container. See
  https://docs.typo3.org/typo3cms/HowToDocument/RenderingDocs/Quickstart.html#rendering-docs-quickstart for explanation
  and help.
* make your changes to the rst files
* rebuild documentation with composer build:doc
* commit/open merge request
