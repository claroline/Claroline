import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineCursusBundle', {
  /**
   * Provides searchable items for the global search.
   */
  search: {
    'training' : () => { return import(/* webpackChunkName: "training-search-training" */ '#/plugin/cursus/search/training')}
  },

  data: {
    types: {
      'course'          : () => { return import(/* webpackChunkName: "training-data-course" */  '#/plugin/cursus/data/types/course') },
      'training_session': () => { return import(/* webpackChunkName: "training-data-session" */ '#/plugin/cursus/data/types/session') }
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
    'trainings'      : () => { return import(/* webpackChunkName: "training-tools-trainings" */       '#/plugin/cursus/tools/trainings') },
    'training_events': () => { return import(/* webpackChunkName: "training-tools-training-events" */ '#/plugin/cursus/tools/events') }
  },

  events: {
    'training_event': () => { return import(/* webpackChunkName: "training-events-event" */ '#/plugin/cursus/events/event') }
  },

  actions: {
    course: {
      'open'      : () => { return import(/* webpackChunkName: "training-action-course-open" */       '#/plugin/cursus/actions/course/open') },
      'edit'      : () => { return import(/* webpackChunkName: "training-action-course-edit" */       '#/plugin/cursus/actions/course/edit') },
      'export-pdf': () => { return import(/* webpackChunkName: "training-action-course-export-pdf" */ '#/plugin/cursus/actions/course/export-pdf') },
      'delete'    : () => { return import(/* webpackChunkName: "training-action-course-delete" */     '#/plugin/cursus/actions/course/delete') }
    }
  },

  restrictions: {
    workspace: {
      'training': () => { return import(/* webpackChunkName: "training-restriction-workspace" */     '#/plugin/cursus/workspace/restriction') }
    }
  }
})
