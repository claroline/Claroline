/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineSlideshowBundle', {
  resources: {
    'claro_slideshow': () => { return import(/* webpackChunkName: "plugin-slideshow-slideshow-resource" */ '#/plugin/slideshow/resources/slideshow') }
  }
})
