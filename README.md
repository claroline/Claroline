# Distribution

[![Build Status](https://travis-ci.org/claroline/Distribution.svg?branch=master)](https://travis-ci.org/claroline/Distribution)

This is the official distribution package of the Claroline Connect learning
management system. It is intended to be installed within the
[claroline/Claroline](http://github.com/claroline/Claroline) application and
provides:

1. the core bundles and components needed to get a working platform;
2. the plugin bundles officially supported by the Claroline Consortium.

## WARNING

This repository is still a work in progress.

## TODO

- [ ] update
- [ ] upgrade from previous packages
- [ ] add LessonBundle
- [ ] add WebsiteBundle
- [ ] add FormulaPluginBundle
- [ ] add PdfGeneratorBundle
- [ ] ensure ExoBundle dependency on BadgeBundle is met (e.g. when enabling/disabling plugins)
- [ ] check if PdfGeneratorBundle is usable before activation (requires wkhtmltopdf)
- [ ] check if LdapBundle is usable before activation (requires ext5-ldap)
- [ ] migrate contents from DevBundle
- [ ] remove the innova/angular bundles (required by portfolio)
- [ ] remove the "innova/angular-ui-pageslide-bundle" (for path)
- [ ] remove the front-end-bundle (required by icapbadge for jqplot and by core/Resouces/less/layout.less)
- [ ] add install and doc sections (require claroline/Claroline to be updated as well)
