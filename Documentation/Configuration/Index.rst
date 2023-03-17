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

.. only:: html

	.. contents::
		:local:
		:depth: 2

.. _configuration-debug:
Debug dimensions and ratio on directly on picture `debug`
""""""""""""""""""""""

.. confval:: debug

   :type: boolean
   :Default: 0

    If set to true, debug info (width, height, ratio) is rendered as annotation directly on the image.

    This setting is not used in the ViewHelpers, instead it is meant to be used in your templates, e.g.

    .. code-block:: html

        <ai:picture debug="{plugin.tx_c1_adaptive_images.settings.debug} ...">

.. _configuration-jsdebug:
Debug dimensions and ratio with JavaScript `jsdebug`
""""""""""""""""""""""""""""""""""""""""""""""""""""

.. confval:: jsdebug

   :type: boolean
   :Default: 0

    If set, then some debug infos (loaded image dimensions, ratio, container width) are calculated via javascript
    and shown near the image (positioning of the debug text with css)

    This setting is not used in the ViewHelpers, instead it is meant to be used in your templates, e.g.

    .. code-block:: html

        <ai:picture jsdebug="{plugin.tx_c1_adaptive_images.settings.jsdebug} ...">

.. _configuration-ratioBox:
Use ratio box `ratioBox`
""""""""""""""""""""""""

.. confval:: ratioBox

   :type: boolean
   :Default: 0

    This setting is not used in the ViewHelpers, instead it is meant to be used in your templates, e.g.

    .. code-block:: html

        <ai:picture ratiobox="{plugin.tx_c1_adaptive_images.settings.ratiobox} ...">

.. _configuration-srcsetWidths:
Default srcset widths `srcsetWidths`
""""""""""""""""""""""""""""""""""""

.. confval:: srcsetWidths

   :type: string
   :Default: '360,768,1024,1440,1920'

    Default value for srcsetWidths in Viewhelpers, when no srcsetWidths Viewhelper argument is given.
