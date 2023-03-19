.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

Target group: **Administrators**

.. _admin-installation:

Installation
------------

To install the extension, perform the following steps:

#. Go to the Extension Manager
#. Install the extension
#. Load the static template

.. _admin-configuration:

Relevant TYPO3_CONF_VARS
------------------------

GFX/processor_allowUpscaling
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If set to false, then images are not upscaled. I.e. if one or more srcset candidates width is bigger than the original
images width, then one last image candidate is created with the original images width.

On the other hand, if this setting is true, then images will be upscaled to match the widths of all given srcset
candidates.

Respecting this setting was introduced with c1_adaptive_images version 0.1.5

Include third-party JavaScript (if needed)
------------------------------------------

This extension does currently not include third party JavaScript which is needed for advanced image modes and the
integrator/administrator has to add them to the website.

lazysizes.js
^^^^^^^^^^^^

Lazysizes.js is needed to render images using lazyloading. It also allows setting the sizes attribute for adaptive
images to 'auto'.

Download it at https://github.com/aFarkas/lazysizes

The script should be included early, e.g.:

.. code-block:: none

    page.includeJSFooterlibs.lazysizes = EXT:yourtheme/Resources/Public/Js/lazysizes.min.js

picturefill
^^^^^^^^^^^

..  note::

    The picturefill polyfill is only needed if you need support for the picture tag in very outdated browsers.

A polyfill to support the *picture* tag in older browsers, most notably IE11 and Opera Mini.
Download it at https://scottjehl.github.io/picturefill/

This script should be included early in the head of the website:

.. code-block:: none

    page.includeJSLibs.picturefill = EXT:yourtheme/Resources/Public/Js/picturefill.min.js

**Hint:** As a simpler alternative one could also use https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/respimg as a
picture polyfill.
