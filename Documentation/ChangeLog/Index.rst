.. include:: ../Includes.txt


.. _changelog:

ChangeLog
=========

v2.0.0
------

- Add TYPO3 v14 support, drop TYPO3 v12 support
- Drop PHP 8.1 support, require PHP ^8.2
- Fix Fluid v5 compatibility:

  - Replace removed ``registerTagAttribute()`` with ``registerArgument()``
  - Add ``initialize()`` to apply tag attributes manually
  - Add ``string`` return type to ``render()`` in ``AbstractImageBasedViewHelper``
  - Fix ``title`` attribute handling: use ``additionalArguments`` (Fluid v5 behaviour change)
  - Migrate PHP-style ``xmlns`` namespace declarations to ``http://typo3.org/ns/`` syntax

- Migrate deprecated ``<INCLUDE_TYPOSCRIPT:>`` to ``@import`` in TypoScript files
- Pin Symfony packages to ``^7`` to avoid PHP >=8.4 requirement pulled in by Symfony v8
- Run Rector modernisations: null defaults, arrow functions, readonly properties, class constants
- Update PHPUnit to ^11, PHPStan to ^2.0
- Testing:

  - Update CI matrix: add TYPO3 ^14.3 with PHP 8.2–8.5, drop ^12.4 / PHP 8.1
  - Fix ``TYPO3_PATH_APP`` to point to project root (required by cms-composer-installers v5)
  - Migrate SQLite path and ``TYPO3_PATH_APP`` in CI, setup scripts and docs
  - Replace remote W3C markup validation with local HTML5 validation
  - Rename TypoScript fixture files from ``.t3s`` to ``.typoscript``
  - Update acceptance tests for v14 behaviour changes (``selectedRatio``, ``excludeFromSync``)

v1.1.1
------

- Add editable site settings, thanks @zenoussi
- Add extension icon, thanks @zenoussi

v1.1.0
------

- Support TYPO3 v13
- Drop support for v11
- Introduce Site-Sets
- Modernize PHP: More typing and constructor DI

v1.0.2
------

- Fix Array to string conversion when adding focus-area (see #22)

v1.0.1
------

- Remove plugin setting mode, this was not used anywhere.
- Deprecate settings srcsetWidthsMobile and srcsetWidthsDesktop.
- Reduce number of default srcset candidates in plugin.tx_c1_adaptive_images.settings.srcsetWidths


v1.0.0
------

- Add TYPO3 v12 support, drop TYPO3 v10 support
- Update tests
- Refactoring


v0.2.0
------

- Support TYPO3 9.5 and 10.4 now. Drop support for 8.7.
- Breaking: rename TypoScript setting plugin.tx_c1_adaptive_images.settings.ratio_box to
  plugin.tx_c1_adaptive_images.settings.ratioBox for consistency
- Fix error when a cropVariant other than default was used, see issue #13.
- Fix exception with missing images, see issue #3.
- Run tests using a sqlite database now








