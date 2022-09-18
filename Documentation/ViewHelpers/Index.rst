.. include:: ../Includes.txt

.. _ViewHelpers:

===========
ViewHelpers
===========

.. contents:: :local:
    :depth: 1

General
-------

Add the fluid namespace declaration to fluid templates
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Before version 0.1.5 registering of the global viewhelper namespace *ai* was not working.

Even if you use a version >= 0.1.5 you might still want to add this namespace declaration to your templates and
partials:

.. code-block:: html

    <html xmlns:ai="C1\AdaptiveImages\ViewHelpers"
          xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
          data-namespace-typo3-fluid="true">
       ...
    </html>

ai:getCropVariants
------------------

Returns a CropVariantCollection as array of cropVariants this FileReference has.

Arguments
^^^^^^^^^

=========== =========== =========== ==================================================================================
argument    required    default     Description
=========== =========== =========== ==================================================================================
file        yes                     FileReference to get the cropVariants from.
asString    no          false       Return the result as string (instaed array)
=========== =========== =========== ==================================================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:getCropVariants file="{file}" />

will return (if the FileReference has two cropVariants):

.. code-block:: none

    array(2 items)
       default => array(7 items)
          id => 'default' (7 chars)
          title => '' (0 chars)
          cropArea => array(4 items)
             x => 0 (double)
             y => 0.09925 (double)
             width => 0.999 (double)
             height => 0.8991 (double)
          allowedAspectRatios => array(empty)
          selectedRatio => NULL
          focusArea => array(4 items)
             x => 0.33333333333333 (double)
             y => 0.33333333333333 (double)
             width => 0.33333333333333 (double)
             height => 0.33333333333333 (double)
          coverAreas => NULL
       mobile => array(7 items)

ai:getSrcSet
------------

Get a srcset string for a given cropVariant and widths and generate images for srcset candidates

Arguments
^^^^^^^^^
=============== =========== =========================== ===============================================================
argument        required    Default                     Description
=============== =========== =========================== ===============================================================
file            yes                                     FileReference to use
cropVariant     no          default                     select a cropping variant, in case multiple croppings have been
                                                        specified or stored in FileReference
aspectRatio     no                                      Enforce a certain aspect ratio. If empty, the aspectRatio is
                                                        generated from the cropVariant.
                                                        Example: 2 for 2:1 ratio, 1.777777778 for 16:9 ratio.
widths          no          [320,640,1024,1440,1920]    create srcset candidates with these widths
debug           no          0                           Add debug output (width, height, ratio) to the generated images
=============== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:getCropVariants file="{file}" />

returns

.. code-block:: none

    /fileadmin/_processed_/7/9/image_2269306f6a.jpg 360w,/fileadmin/_processed_/7/9/image_5f0de63291.jpg 720w

or for cropVariant mobile and widths as array

.. code-block:: html

    <ai:getSrcset file="{file}" cropVariant="mobile" widths="[360,720]" debug="1" />

returns

.. code-block:: none

    /fileadmin/_processed_/7/9/image_cbb4289869.jpg 360w,/fileadmin/_processed_/7/9/image_3e7a2d9258.jpg 720w


ai:ratioBox
-----------

Wraps an image or picture tag in a ratio box. This also adds generated css style to the header of the page to set the
correct padding-bottom to always maintain the ratio and thus prevent page reflows.

Arguments
^^^^^^^^^
=============== =========== =========================== ===============================================================
argument        required    Default                     Description
=============== =========== =========================== ===============================================================
file            yes                                     FileReference to use
mediaQueries    no          [['default' => '']]         Array of arrays containing ratio and media for cropVariants
=============== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:ratioBox file="{file}" mediaQueries="{mobile: '(max-width:767px)', default: ''}">
        <f:comment>Your picture/image tag (f:image, ai:image etc.)</f:comment>
    </ai:ratioBox>

Assuming that the image has cropVariants default (16:9) and mobile (4:3) this will add css style to the head of the
website and return:

.. code-block:: html

 <div class="rb rb--62dot5 rb--max-width767px-75">
   <f:comment>Your picture/image tag (f:image, ai:image etc.)</f:comment>
 </div>

ai:placeholder.image
--------------------

Returns a placeholder image (base64 encoded data OR uri) width reduced quality and size, but original aspect ratio.

Arguments
^^^^^^^^^
=============== =========== =========================== ===============================================================
argument        required    Default                     Description
=============== =========== =========================== ===============================================================
file            yes                                     FileReference to use
cropVariant     no          default                     select a cropping variant, in case multiple croppings have been
                                                        specified or stored in FileReference
width           no          128                         create placeholder image with this width
height          no                                      create placeholder image with this height
aspectRatio     no                                      Enforce a certain aspect ratio. If empty, the aspectRatio is
                                                        generated from the cropVariant.
                                                        Example: 2 for 2:1 ratio, 1.777777778 for 16:9 ratio.
absolute        no          false                       Force absolute URL
dataUri         no          true                        Returns the base64 encoded dataUri of the image
                                                        (for inline usage)

=============== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:placeholder.image file="{file}" cropVariant="mobile" width="192" />

returns the images as base64 encoded data-uri

.. code-block:: none

    data:image/jpeg;base64,/9j/4AAQSkZJ[...]

or return image uri instead:

.. code-block:: html

    <ai:placeholder.image file="{file}" cropVariant="mobile" width="192" dataUri="0" />

returns

.. code-block:: none

    /fileadmin/_processed_/7/9/image_702e24791e.jpg

ai:placeholder.svg
------------------

Returns a placeholder SVG image (base64 encoded data uri) keeping original aspect ratio by replacing the SVG's width/and
height of that of the generated image.

Arguments
^^^^^^^^^

=============== =========== =========================== ===============================================================
argument        required    Default                     Description
=============== =========== =========================== ===============================================================
file            yes                                     FileReference to use
cropVariant     no          default                     select a cropping variant, in case multiple croppings have been
                                                        specified or stored in FileReference
aspectRatio     no                                      Enforce a certain aspect ratio. If empty, the aspectRatio is
                                                        generated from the cropVariant.
                                                        Example: 2 for 2:1 ratio, 1.777777778 for 16:9 ratio.
=============== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:placeholder.svg file="{file}" cropVariant="mobile"/>

returns the SVG as base64 encoded data-uri

.. code-block:: none

    data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0[...]

ai:image
--------

This viewHelper outputs a complete adaptive image *img* tag, optionally with ratio box and a placeholder image.
Use this when you don't need art direction/different cropVariants. If you need art direction see
:ref:`aiPictureViewHelper`.

Arguments
^^^^^^^^^

This viewHelper has all arguments which are available to the
`f:image viewHelper <https://docs.typo3.org/typo3cms/ExtbaseGuide/Fluid/ViewHelper/Image.html>`_
including `fluids universal tag attributes <https://docs.typo3.org/typo3cms/ExtbaseGuide/Fluid/UniversalTagAttributes.html#universaltagattributes>`_
plus the following:

================== =========== =========================== ===============================================================
argument           required    Default                     Description
================== =========== =========================== ===============================================================
lazy               no          false                       lazy load images and auto-sizes with lazysizes.js
debug              no          false                       Add debug output (width, height, ratio) to the generated images using IM/GM
jsdebug            no          false                       Add debug output (width, height, ratio) near the image using javascript
srcsetWidths       no          [320,640,1024,1440,1920]    create srcset candidates with these widths
aspectRatio        no                                      Enforce a certain aspect ratio. If empty, the aspectRatio is
                                                           generated from the cropVariant.
                                                           Example: 2 for 2:1 ratio, 1.777777778 for 16:9 ratio.
cropVariant        no          default                     select a cropping variant, in case multiple croppings have been
                                                           specified or stored in FileReference
sizes              no          100vw                       sizes attribute for the img tag.
                                                           Takes precedence over additionalAttributes["sizes"] if both are given.
placeholderInline  no          true                        Include placeholder inline in HTML (base64 encoded)
ratiobox           no          false                       The image is wrapped in a ratio box if true.
================== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:image image="{file}"
          class="img-responsive lazyload"
          width="{dimensions.width}"
          height="{dimensions.height}"
          alt="{file.alternative}"
          title="{file.title}"
          srcsetWidths="320,640"
          placeholderInline="1"
          placeholderWidth="128"
          lazy="1"
          debug="1"
          ratiobox="1"
          jsdebug="1"
          sizes="100vw"
    />

returns a complete image tag with lazysizes loading and a placeholder image.
**Important** For lazysizes to work you have to add the class *lazyload* here.

.. _aiPictureViewHelper:

ai:picture
----------

This viewHelper outputs a complete adaptive image *picture* tag, optionally with ratio box and a placeholder image.
If you need to show different cropVariants for different device widths you need to use this viewHelper.

Arguments
^^^^^^^^^

This viewHelper has all arguments which are available to the
`f:image viewHelper <https://docs.typo3.org/typo3cms/ExtbaseGuide/Fluid/ViewHelper/Image.html>`_
including `fluids universal tag attributes <https://docs.typo3.org/typo3cms/ExtbaseGuide/Fluid/UniversalTagAttributes.html#universaltagattributes>`_
plus the following:

================== =========== =========================== ===============================================================
argument           required    Default                     Description
================== =========== =========================== ===============================================================
lazy               no          false                       lazy load images and auto-sizes with lazysizes.js
debug              no          false                       Add debug output (width, height, ratio) to the generated images using IM/GM
jsdebug            no          false                       Add debug output (width, height, ratio) near the image using javascript
srcsetWidths       no          [320,640,1024,1440,1920]    create srcset candidates with these widths
aspectRatio        no                                      Enforce a certain aspect ratio. If empty, the aspectRatio is
                                                           generated from the cropVariant.
                                                           Example: 2 for 2:1 ratio, 1.777777778 for 16:9 ratio.
cropVariant        no          default                     select a cropping variant, in case multiple croppings have been
                                                           specified or stored in FileReference
sizes              no          100vw                       sizes attribute for the img tag.
                                                           Takes precedence over additionalAttributes["sizes"] if both are given.
placeholderInline  no          true                        Include placeholder inline in HTML (base64 encoded)
ratiobox           no          false                       The image is wrapped in a ratio box if true.
sources            no          [['default' => '']]         Array of arrays containing candidates for source tags
================== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:picture image="{file}"
          class="img-responsive-full lazyload"
            width="{dimensions.width}"
            height="{dimensions.height}"
            alt="{file.alternative}"
            title="{file.title}"
            sources="{
                'mobile': {
                    'srcsetWidths': '320,640,768',
                    'media': '(max-width: 767px)'
                }
              }"
            srcsetWidths="768,1024"
            placeholderInline="1"
            placeholderWidth="128"
            lazy="1"
            debug="1"
            ratiobox="1"
            jsdebug="1"
            sizes="100vw"
    />

returns a complete picture tag with one source for the cropVariant mobile. With lazysizes loading and a placeholder image.
**Important** For lazysizes to work you have to add the class *lazyload* here.

.. code-block:: html

    <ai:picture image="{file}"
          class="img-responsive-full lazyload"
            width="{dimensions.width}"
            height="{dimensions.height}"
            alt="{file.alternative}"
            title="{file.title}"
            sources="{
                'mobile': {
                    'srcsetWidths': '320,640,768',
                    'media': '(max-width: 767px)',
                    'aspectRatio': 1
                }
              }"
            srcsetWidths="768,1024"
            placeholderInline="1"
            placeholderWidth="128"
            lazy="1"
            debug="1"
            ratiobox="1"
            jsdebug="1"
            sizes="100vw",
            aspectRatio: 2
    />

Returns a rendered picture tag but enforces an aspect ratio of 2:1 for Default and 1:1 for mobile.
