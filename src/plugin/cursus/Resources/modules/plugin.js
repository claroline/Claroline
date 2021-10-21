import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineCursusBundle', {
  /**
   * Provides tab types for Home tools.
   */
  home: {
    'training_catalog': () => { return import(/* webpackChunkName: "cursus-home-catalog" */ '#/plugin/cursus/home/catalog') }
  },

  /**
   * Provides searchable items for the global search.
   */
  search: {
    'training' : () => { return import(/* webpackChunkName: "cursus-search-training" */ '#/plugin/cursus/search/training')}
  },

  data: {
    types: {
      'course'          : () => { return import(/* webpackChunkName: "cursus-data-course" */  '#/plugin/cursus/data/types/course') },
      'training_session': () => { return import(/* webpackChunkName: "cursus-data-session" */ '#/plugin/cursus/data/types/session') }
    },
    sources: {
      'all_courses'           : () => { return import(/* webpackChunkName: "cursus-data-all-courses" */     '#/plugin/cursus/data/sources/courses') },
      'public_course_sessions': () => { return import(/* webpackChunkName: "cursus-data-public-sessions" */ '#/plugin/cursus/data/sources/sessions') },
      'course_sessions'       : () => { return import(/* webpackChunkName: "cursus-data-sessions" */        '#/plugin/cursus/data/sources/sessions') },
      'my_course_sessions'    : () => { return import(/* webpackChunkName: "cursus-data-my-sessions" */     '#/plugin/cursus/data/sources/my-sessions') },
      'training_events'       : () => { return import(/* webpackChunkName: "cursus-data-events" */          '#/plugin/cursus/data/sources/events') },
      'my_training_events'    : () => { return import(/* webpackChunkName: "cursus-data-my-events" */       '#/plugin/cursus/data/sources/events') }
    }
  },

  tools: {
    'trainings'      : () => { return import(/* webpackChunkName: "cursus-tools-trainings" */       '#/plugin/cursus/tools/trainings') },
    'training_events': () => { return import(/* webpackChunkName: "cursus-tools-training-events" */ '#/plugin/cursus/tools/events') }
  },

  events: {
    'training_event': () => { return import(/* webpackChunkName: "cursus-events-event" */ '#/plugin/cursus/events/event') }
  }
})
