.. include:: ../Includes.txt

.. _developer:

================
Developer Corner
================

.. toctree::
	:maxdepth: 5
	:titlesonly:

        ViewHelpers/Index


.. _developer-hooks:

Hooks
-----

Possible hook examples. Input parameters are:

+----------------+---------------+---------------------------------+
| Parameter      | Data type     | Description                     |
+================+===============+=================================+
| $table         | string        | Name of the table               |
+----------------+---------------+---------------------------------+
| $field         | string        | Name of the field               |
+----------------+---------------+---------------------------------+

Use parameter :code:`$table` to retrieve the table name...

.. _developer-api:

API
---

How to use the API...

.. code-block:: php

	$stuff = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
		'\\Foo\\Bar\\Utility\\Stuff'
	);
	$stuff->do();

or some other language:

.. code-block:: javascript
   :linenos:
   :emphasize-lines: 2-4

	$(document).ready(
		function () {
			doStuff();
		}
	);
