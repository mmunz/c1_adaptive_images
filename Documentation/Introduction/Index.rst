.. include:: ../Includes.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

This extension brings a set of Fluid ViewHelpers that are useful for adaptive image rendering.



.. note::

   To use this extension, you should already have a good understanding of Fluid and adaptive images (i.e. know what
   picture, srcset and sizes are and how to use them).


"Higher level" viewhelpers
^^^^^^^^^^^^^^^^^^^^^^^^^^

The viewhelpers :ref:`ai:image <ai-image-viewhelper>` and :ref:`ai:picture <ai-picture-viewhelper>` can be used as a
replacement for the f:image viewhelper.

=========================================== ===========================
:ref:`ai:image <ai-image-viewhelper>`       render an adaptive image
:ref:`ai:picture <ai-picture-viewhelper>`   render an adaptive picture
=========================================== ===========================

"Lower level" viewhelpers
^^^^^^^^^^^^^^^^^^^^^^^^^

Besides those "higher level" viewhelpers there are also viewhelpers, that allow for more experimantation and maximum
flexibility. those may be used in combination with Fluids f:image and f:media viewhelpers.

==================================================================  ============================================================
:ref:`ai:getCropVariants <ai-get-crop-variants-viewhelper>`         Returns a CropVariantCollection as array for a FileReference
:ref:`ai:getSrcSet <ai-get-srcset-viewhelper>`                      Get a srcset string for a given cropVariant and widths and
                                                                    generate images for srcset candidates
:ref:`ai:ratioBox <ai-ratiobox-viewhelper>`                         Wraps an image or picture tag in a ratio box
:ref:`ai:placeholder.image <ai-placeholder-image-viewhelper>`       Returns a placeholder image
:ref:`ai:placeholder.svg <ai-placeholder-svg-viewhelper>`           Returns a placeholder SVG image
==================================================================  ============================================================

.. _demo-page:

Demo Page
---------

There are many pitfalls while using adaptive images and also a lot of options for implementing them. To experiment and
test different possibilities there is a demo page using this extension which might give you an idea of different
implementations and their problems.

Demo Page for adaptive images: https://ai-demo.comuno.net/

