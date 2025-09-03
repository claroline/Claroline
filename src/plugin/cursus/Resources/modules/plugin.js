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
      'training_course' : () => { return import(/* webpackChunkName: "training-data-course" */  '#/plugin/cursus/data/types/course') },
      'training_event'  : () => { return import(/* webpackChunkName: "training-data-session" */ '#/plugin/cursus/data/types/event') },
      'training_session': () => { return import(/* webpackChunkName: "training-data-session" */ '#/plugin/cursus/data/types/session') }
    },
    sources: {
      'all_courses'           : () => { return import(/* webpackChunkName: "cursus-data-all-courses" */     '#/plugin/cursus/data/sources/courses') },
      'course_sessions'       : () => { return import(/* webpackChunkName: "cursus-data-sessions" */        '#/plugin/cursus/data/sources/sessions') },
      'my_course_sessions'    : () => { return import(/* webpackChunkName: "cursus-data-my-sessions" */     '#/plugin/cursus/data/sources/my-sessions') },
      'training_events'       : () => { return import(/* webpackChunkName: "cursus-data-events" */          '#/plugin/cursus/data/sources/events') },
      'my_training_events'    : () => { return import(/* webpackChunkName: "cursus-data-my-events" */       '#/plugin/cursus/data/sources/events') },
      'event_presences'       : () => { return import(/* webpackChunkName: "cursus-data-event-presences" */ '#/plugin/cursus/data/sources/event-presences') },
      'my_event_presences'    : () => { return import(/* webpackChunkName: "cursus-data-my-presences" */    '#/plugin/cursus/data/sources/my-event-presences') }
    }
  },

  tools: {
    'trainings': () => { return import(/* webpackChunkName: "training-tools-trainings" */ '#/plugin/cursus/tools/trainings') },
    'presence' : () => { return import(/* webpackChunkName: "training-tools-presence" */  '#/plugin/cursus/tools/presence') },
    'catalog'  : () => { return import(/* webpackChunkName: "training-tools-catalog" */   '#/plugin/cursus/tools/catalog') }
  },

  events: {
    'training_event': () => { return import(/* webpackChunkName: "training-events-event" */ '#/plugin/cursus/events/event') }
  },

  actions: {
    training_course: {
      'open'      : () => { return import(/* webpackChunkName: "training-action-course-open" */       '#/plugin/cursus/actions/course/open') },
      'edit'      : () => { return import(/* webpackChunkName: "training-action-course-edit" */       '#/plugin/cursus/actions/course/edit') },
      'export-pdf': () => { return import(/* webpackChunkName: "training-action-course-export-pdf" */ '#/plugin/cursus/actions/course/export-pdf') },
      'archive'   : () => { return import(/* webpackChunkName: "training-action-course-archive" */    '#/plugin/cursus/actions/course/archive') },
      'restore'   : () => { return import(/* webpackChunkName: "training-action-course-restore" */    '#/plugin/cursus/actions/course/restore') },
      'copy'      : () => { return import(/* webpackChunkName: "training-action-course-copy" */       '#/plugin/cursus/actions/course/copy') },
      'delete'    : () => { return import(/* webpackChunkName: "training-action-course-delete" */     '#/plugin/cursus/actions/course/delete') },
      'add-users' : () => { return import(/* webpackChunkName: "training-action-session-add-users" */ '#/plugin/cursus/actions/course/add-users') }
    },
    training_session: {
      'open'          : () => { return import(/* webpackChunkName: "training-action-session-open" */           '#/plugin/cursus/actions/session/open') },
      'edit'          : () => { return import(/* webpackChunkName: "training-action-session-edit" */           '#/plugin/cursus/actions/session/edit') },
      'export-pdf'    : () => { return import(/* webpackChunkName: "training-action-session-export-pdf" */     '#/plugin/cursus/actions/session/export-pdf') },
      'cancel'        : () => { return import(/* webpackChunkName: "training-action-session-cancel" */         '#/plugin/cursus/actions/session/cancel') },
      'copy'          : () => { return import(/* webpackChunkName: "training-action-session-copy" */           '#/plugin/cursus/actions/session/copy') },
      'open-workspace': () => { return import(/* webpackChunkName: "training-action-session-open-workspace" */ '#/plugin/cursus/actions/session/open-workspace') },
      'register-workspace': () => { return import(/* webpackChunkName: "training-action-session-register-workspace" */ '#/plugin/cursus/actions/session/register-workspace') },
      'delete'        : () => { return import(/* webpackChunkName: "training-action-session-delete" */         '#/plugin/cursus/actions/session/delete') },
      'add-users'     : () => { return import(/* webpackChunkName: "training-action-session-add-users" */      '#/plugin/cursus/actions/session/add-users') }
    },
    training_session_registration: {
      'open'        : () => { return import(/* webpackChunkName: "training-action-session_registration-open" */         '#/plugin/cursus/actions/session_registration/open') },
      'edit'        : () => { return import(/* webpackChunkName: "training-action-session_registration-edit" */         '#/plugin/cursus/actions/session_registration/edit') },
      'delete'      : () => { return import(/* webpackChunkName: "training-action-session_registration-delete" */       '#/plugin/cursus/actions/session_registration/delete') },
      'confirm'     : () => { return import(/* webpackChunkName: "training-action-session_registration-confirm" */      '#/plugin/cursus/actions/session_registration/confirm') },
      'invite'      : () => { return import(/* webpackChunkName: "training-action-session_registration-invite" */       '#/plugin/cursus/actions/session_registration/invite') },
      'move'        : () => { return import(/* webpackChunkName: "training-action-session_registration-move" */         '#/plugin/cursus/actions/session_registration/move') },
      'move-pending': () => { return import(/* webpackChunkName: "training-action-session_registration-move-pending" */ '#/plugin/cursus/actions/session_registration/move-pending') },
      'validate'    : () => { return import(/* webpackChunkName: "training-action-session_registration-validate" */     '#/plugin/cursus/actions/session_registration/validate') }
    },
    training_event: {
      'open'                  : () => { return import(/* webpackChunkName: "training-action-event-open" */            '#/plugin/cursus/actions/event/open') },
      'edit'                  : () => { return import(/* webpackChunkName: "training-action-event-edit" */            '#/plugin/cursus/actions/event/edit') },
      'export-pdf'            : () => { return import(/* webpackChunkName: "training-action-event-export-pdf" */      '#/plugin/cursus/actions/event/export-pdf') },
      'export-ics'            : () => { return import(/* webpackChunkName: "training-action-event-export-ics" */      '#/plugin/cursus/actions/event/export-ics') },
      'export-presence-empty' : () => { return import(/* webpackChunkName: "training-action-event-presence-empty" */  '#/plugin/cursus/actions/event/export-presence-empty') },
      'export-presence-filled': () => { return import(/* webpackChunkName: "training-action-event-presence-filled" */ '#/plugin/cursus/actions/event/export-presence-filled') },
      'copy'                  : () => { return import(/* webpackChunkName: "training-action-event-copy" */            '#/plugin/cursus/actions/event/copy') },
      'delete'                : () => { return import(/* webpackChunkName: "training-action-event-delete" */          '#/plugin/cursus/actions/event/delete') },
      'confirm-status'        : () => { return import(/* webpackChunkName: "training-action-event-confirm-status" */  '#/plugin/cursus/actions/event/confirm-status') },
      'add-users'             : () => { return import(/* webpackChunkName: "training-action-event-add-users" */       '#/plugin/cursus/actions/event/add-users') }
    },
    training_event_registration: {
      'open'                   : () => { return import(/* webpackChunkName: "training-action-event_registration-open" */   '#/plugin/cursus/actions/event_registration/open') },
      'delete'                 : () => { return import(/* webpackChunkName: "training-action-event_registration-delete" */ '#/plugin/cursus/actions/event_registration/delete') },
      'invite'                 : () => { return import(/* webpackChunkName: "training-action-event_registration-invite" */ '#/plugin/cursus/actions/event_registration/invite') },
      'add-evidence'           : () => { return import(/* webpackChunkName: "training-action-event_registration-add-evidence" */ '#/plugin/cursus/actions/event_registration/add-evidence') },
      'delete-evidence'        : () => { return import(/* webpackChunkName: "training-action-event_registration-delete-evidence" */ '#/plugin/cursus/actions/event_registration/delete-evidence') },
      'download-presence'      : () => { return import(/* webpackChunkName: "training-action-event_registration-download-presence" */ '#/plugin/cursus/actions/event_registration/download-presence') },
      'mark-absent-justified'  : () => { return import(/* webpackChunkName: "training-action-event_registration-absent-justified" */ '#/plugin/cursus/actions/event_registration/mark-absent-justified') },
      'mark-absent-unjustified': () => { return import(/* webpackChunkName: "training-action-event_registration-absent-unjustified" */ '#/plugin/cursus/actions/event_registration/mark-absent-unjustified') },
      'mark-present'           : () => { return import(/* webpackChunkName: "training-action-event_registration-present" */ '#/plugin/cursus/actions/event_registration/mark-present') },
      'mark-unknown'           : () => { return import(/* webpackChunkName: "training-action-event_registration-unknown" */ '#/plugin/cursus/actions/event_registration/mark-unknown') },
    },
    training_presence: {
      'export-pdf'             : () => { return import(/* webpackChunkName: "training-action-presence-export-pdf" */ '#/plugin/cursus/actions/presence/export-pdf') },
      'mark-absent-justified'  : () => { return import(/* webpackChunkName: "training-action-presence-absent-justified" */ '#/plugin/cursus/actions/presence/mark-absent-justified') },
      'mark-absent-unjustified': () => { return import(/* webpackChunkName: "training-action-presence-absent-unjustified" */ '#/plugin/cursus/actions/presence/mark-absent-unjustified') },
      'mark-absent-present'    : () => { return import(/* webpackChunkName: "training-action-presence-present" */ '#/plugin/cursus/actions/presence/mark-present') },
      'mark-unknown'           : () => { return import(/* webpackChunkName: "training-action-presence-unknown" */ '#/plugin/cursus/actions/presence/mark-unknown') },
      'add-evidence'           : () => { return import(/* webpackChunkName: "training-action-presence-add-evidence" */ '#/plugin/cursus/actions/presence/add-evidence') },
      'delete-evidence'        : () => { return import(/* webpackChunkName: "training-action-presence-delete-evidence" */ '#/plugin/cursus/actions/presence/delete-evidence') }
    }
  },

  badge_rules: {
    'training_course_presence': () => { return import(/* webpackChunkName: "training-badge_rule-course_presence" */ '#/plugin/cursus/badge_rules/course_presence') },
    'training_event_presence': () => { return import(/* webpackChunkName: "training-badge_rule-event_presence" */ '#/plugin/cursus/badge_rules/event_presence') }
  },

  restrictions: {
    workspace: {
      'training': () => { return import(/* webpackChunkName: "training-restriction-workspace" */ '#/plugin/cursus/workspace') }
    }
  }
})
