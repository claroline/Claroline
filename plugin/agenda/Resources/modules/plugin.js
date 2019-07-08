/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineAgendaBundle', {
  data: {
    sources: {
      'events' : () => { return import(/* webpackChunkName: "agenda-data-events" */  '#/plugin/agenda/data/sources/events') },
      'tasks' : () => { return import(/* webpackChunkName: "agenda-data-tasks" */  '#/plugin/agenda/data/sources/tasks') }
    }
  },

  tools: {
    'agenda_': () => { return import(/* webpackChunkName: "agenda-tool-agenda" */ '#/plugin/agenda/tools/agenda') }
  }
})
