import {registry} from '#/main/app/plugins/registry'

registry.add('cursus', {
  data: {
    types: {
      'course': () => { return import(/* webpackChunkName: "plugin-cursus-data-course" */ '#/plugin/cursus/data/types/course') }
    },
    sources: {
      'public_course_sessions' : () => { return import(/* webpackChunkName: "plugin-cursus-data-resources" */ '#/plugin/cursus/data/sources/sessions') },
      'my_course_sessions'     : () => { return import(/* webpackChunkName: "plugin-cursus-data-resources" */ '#/plugin/cursus/data/sources/my-sessions') }
    }
  },
  tools: {
    'trainings'      : () => { return import(/* webpackChunkName: "plugin-cursus-tools-trainings" */       '#/plugin/cursus/tools/trainings') },
    'training_events': () => { return import(/* webpackChunkName: "plugin-cursus-tools-training-events" */ '#/plugin/cursus/tools/events') }
  }
})