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
see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/GlobalValues/Typo3ConfVars/Index.html

GFX/processor_allowUpscaling
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
If set to false, then images are not upscaled. I.e. if one or more srcset candidates width is bigger than the original
images width, then one last image candidate is created with the original images width.

On the other hand, if this setting is true, then images will be upscaled to match the widths of all given srcset
candidates.

Respecting this setting was introduced with c1_adaptive_images version 0.1.5

