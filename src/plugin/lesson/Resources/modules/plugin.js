/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('IcapLessonBundle', {
  actions: {
    resource: {
      'chapter' : () => { return import(/* webpackChunkName: "plugin-lesson-action-add-chapter" */ '#/plugin/lesson/resources/lesson/actions/add-chapter') }
    }
  },

  resources: {
    'icap_lesson': () => { return import(/* webpackChunkName: "plugin-lesson-lesson-resource" */ '#/plugin/lesson/resources/lesson') }
  }
})
