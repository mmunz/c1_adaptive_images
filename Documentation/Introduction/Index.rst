.. include:: ../Includes.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

This extension brings a set of Fluid ViewHelpers that are useful for adaptive image rendering.

By using these additional ViewHelpers in combination with Fluids f:image and f:media ViewHelpers rendering of
adaptive images becomes much easier. Using this simple approach (just using ViewHelpers, not registering a custom
ImageRenderer class, which would also be possible) the code stays really simple while at the same time the developer
keeps maximum flexibility in generating the picture or image tag using the commun Fluid ViewHelpers.


.. note::

   To use this extension, you should already have a good understanding of Fluid and adaptive images (i.e. know what
   picture, srcset and sizes are and how to use them).
