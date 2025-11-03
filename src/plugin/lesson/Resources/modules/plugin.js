/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('IcapLessonBundle', {
  resources: {
    'icap_lesson': () => { return import(/* webpackChunkName: "lesson-resource-lesson" */ '#/plugin/lesson/resources/lesson') }
  },
  data: {
    sources: {
      'lesson_chapters': () => { return import(/* webpackChunkName: "lesson-data-announcements" */ '#/plugin/lesson/data/sources/chapters') }
    }
  }
})
