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
    'cursus'                        : () => { return import(/* webpackChunkName: "plugin-cursus-tools-cursus" */         '#/plugin/cursus/tools/cursus') },
    'claroline_session_events_tool' : () => { return import(/* webpackChunkName: "plugin-cursus-tools-session-events" */ '#/plugin/cursus/tools/session-events-tool') }
  },
  administration: {
    'claroline_cursus_tool' : () => { return import(/* webpackChunkName: "plugin-cursus-admininstration-cursus-tool" */ '#/plugin/cursus/administration/cursus') }
  }
})