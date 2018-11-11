.. include:: ../Includes.txt


.. _configuration:

Configuration Reference
=======================

Target group: **Developers**


.. _configuration-typoscript:

TypoScript Reference
--------------------

Possible subsections: Reference of TypoScript options.
The construct below show the recommended structure for
TypoScript properties listing and description.

Properties should be listed in the order in which they
are executed by your extension, but the first should be
alphabetical for easier access.

When detailing data types or standard TypoScript
features, don't hesitate to cross-link to the TypoScript
Reference as shown below. See the :file:`Settings.yml`
file for the declaration of cross-linking keys.


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

:typoscript:`plugin.tx_c1_adaptive_images.debug =` :ref:`t3tsref:data-type-boolean`

If set, debug info (width, height, ratio) is rendered as annotation directly on the image.

jsdebug
"""""

:typoscript:`plugin.tx_c1_adaptive_images.jsdebug =` :ref:`t3tsref:data-type-boolean`

If set, then some debug infos (loaded image dimensions, ratio, container width) are calculated via javascript
and shown near the image (positioning of the debug text with css)

.. _configuration-faq:

FAQ
---

Possible subsection: FAQ
