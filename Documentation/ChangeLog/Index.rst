.. include:: ../Includes.txt


.. _changelog:

ChangeLog
=========

v0.2.0
------

- Support TYPO3 9.5 and 10.4 now. Drop support for 8.7.
- Breaking: rename TypoScript setting plugin.tx_c1_adaptive_images.settings.ratio_box to
  plugin.tx_c1_adaptive_images.settings.ratioBox for consistency
- Fix error when a cropVariant other than default was used, see issue #13.
- Fix exception with missing images, see issue #3.
- Run tests using a sqlite database now





