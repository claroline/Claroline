/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineAgendaBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    //'clock': () => { return import(/* webpackChunkName: "agenda-header-clock" */ '#/plugin/agenda/header/clock') }
  },

  data: {
    sources: {
      'events': () => { return import(/* webpackChunkName: "agenda-data-events" */ '#/plugin/agenda/data/sources/events') },
      'tasks' : () => { return import(/* webpackChunkName: "agenda-data-tasks" */  '#/plugin/agenda/data/sources/tasks') }
    }
  },

  tools: {
    'agenda': () => { return import(/* webpackChunkName: "agenda-tools-agenda" */ '#/plugin/agenda/tools/agenda') }
  },

  events: {
    'event': () => { return import(/* webpackChunkName: "agenda-events-event" */ '#/plugin/agenda/events/event') },
    'task' : () => { return import(/* webpackChunkName: "agenda-events-task" */  '#/plugin/agenda/events/task') }
  }
})
