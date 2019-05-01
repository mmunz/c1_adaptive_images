.. include:: ../Includes.txt


.. _configuration:

Configuration Reference
=======================

Target group: **Developers**


.. _configuration-typoscript:

TypoScript Reference
--------------------

These typoscript settings are meant as defaults and can be overwritten by viewHelper arguments.

Properties
^^^^^^^^^^

.. container:: ts-properties

	=========================== ===================================== ======================= ====================
	Property                    Data type                             :ref:`t3tsref:stdwrap`  Default
	=========================== ===================================== ======================= ====================
	debug_                       :ref:`t3tsref:data-type-boolean`      no                     :code:`0`
	jsdebug_                     :ref:`t3tsref:data-type-boolean`      no                     :code:`0`
	=========================== ===================================== ======================= ====================


Property details
^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


debug
"""""

:typoscript:`plugin.tx_c1_adaptive_images.settings.debug =` :ref:`t3tsref:data-type-boolean`

If set, debug info (width, height, ratio) is rendered as annotation directly on the image.

jsdebug
"""""""

:typoscript:`plugin.tx_c1_adaptive_images.settings.jsdebug =` :ref:`t3tsref:data-type-boolean`

If set, then some debug infos (loaded image dimensions, ratio, container width) are calculated via javascript
and shown near the image (positioning of the debug text with css)
