.. include:: ../Includes.txt


.. _known-problems:

Known Problems
==============

High server load because of image processing
--------------------------------------------

When TYPO3 renders a page for the first time, all images on the pages are processed at once. After that, the generated
images are cached on the filesystem.

Because this extension creates different sizes of each image, this can quickly escalate, especially when many images
with a lot of different sizes are on the page and there are many concurrent requests.

In practice this is not a big problem, as long as you do not clear all generated images.

As long as TYPO3 itself does not support asynchronous image rendering there are some possible workarounds for this
problem, if you really want to go that way:

- Render images async with `EXT:deferred_image_processing <https://extensions.typo3.org/extension/deferred_image_processing>`__
- Use external proxy services for image processing, e.g. Cloudflare or AWS cloudfront to offload the processing work
- Use vips as faster alternative for image processing. Unfortunately there is currently no fully working implementation
  available for TYPO3, but for inspiration have a look at `EXT:vips <https://github.com/christophlehmann/vips>`__.
- Another interesting approach, also by Christoph Lehmann is `EXT:imgproxy <https://github.com/christophlehmann/imgproxy>`__
  which uses a locally hosted imgproxy (which also uses vips and is quite fast)




