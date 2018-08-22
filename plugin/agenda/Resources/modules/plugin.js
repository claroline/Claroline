/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('agenda', {
  data: {
    sources: {
      'events' : () => { return import(/* webpackChunkName: "agenda-data-events" */  '#/plugin/agenda/data/sources/events') },
      'tasks' : () => { return import(/* webpackChunkName: "agenda-data-tasks" */  '#/plugin/agenda/data/sources/tasks') }
    }
  }
})
