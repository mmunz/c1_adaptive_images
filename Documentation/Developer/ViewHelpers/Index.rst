.. include:: ../Includes.txt

.. _ViewHelpers:

===========
ViewHelpers
===========

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

    /fileadmin/_processed_/7/9/image_cbb4289869.jpg 0w,/fileadmin/_processed_/7/9/image_3e7a2d9258.jpg 720w


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
=============== =========== =========================== ===============================================================

Examples
^^^^^^^^

.. code-block:: html

    <ai:placeholder.svg file="{file}" cropVariant="mobile"/>

returns the SVG as base64 encoded data-uri

.. code-block:: none

    data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0[...]




