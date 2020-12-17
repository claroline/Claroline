import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineCursusBundle', {
  /**
   * Provides tab types for Home tools.
   */
  home: {
    //'training_catalog': () => { return import(/* webpackChunkName: "plugin-cursus-home-catalog" */ '#/plugin/cursus/home/catalog') }
  },

  data: {
    types: {
      'course': () => { return import(/* webpackChunkName: "plugin-cursus-data-course" */ '#/plugin/cursus/data/types/course') }
    },
    sources: {
      'public_course_sessions' : () => { return import(/* webpackChunkName: "plugin-cursus-data-public-sessions" */ '#/plugin/cursus/data/sources/sessions') },
      'my_course_sessions'     : () => { return import(/* webpackChunkName: "plugin-cursus-data-my-sessions" */     '#/plugin/cursus/data/sources/my-sessions') }
    }
  },

  tools: {
    'trainings'      : () => { return import(/* webpackChunkName: "plugin-cursus-tools-trainings" */       '#/plugin/cursus/tools/trainings') },
    'training_events': () => { return import(/* webpackChunkName: "plugin-cursus-tools-training-events" */ '#/plugin/cursus/tools/events') }
  }
})
