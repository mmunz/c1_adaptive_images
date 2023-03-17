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

[...]

v1.0.0
- Add TYPO3 v12 support, drop TYPO3 v10 support
- Update tests
- Refactoring

v1.0.1
- Remove plugin setting mode, this was not used anywhere.
- Deprecate settings srcsetWidthsMobile and srcsetWidthsDesktop.
- Reduce number of default srcset candidates in plugin.tx_c1_adaptive_images.settings.srcsetWidths






