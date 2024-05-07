/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('IcapLessonBundle', {
  resources: {
    'icap_lesson': () => { return import(/* webpackChunkName: "plugin-lesson-lesson-resource" */ '#/plugin/lesson/resources/lesson') }
  }
})
