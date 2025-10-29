/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineAgendaBundle', {
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
  },

  actions: {
    agenda_event: {
      'open': () => { return import(/* webpackChunkName: "agenda-action-event-open" */ '#/plugin/agenda/actions/agenda_event/open') },
      'edit': () => { return import(/* webpackChunkName: "agenda-action-event-edit" */ '#/plugin/agenda/actions/agenda_event/edit') },
      'export-ics': () => { return import(/* webpackChunkName: "agenda-action-event-export-ics" */ '#/plugin/agenda/actions/agenda_event/export-ics') },
      'send-invitations': () => { return import(/* webpackChunkName: "agenda-action-event-send-invitations" */ '#/plugin/agenda/actions/agenda_event/send-invitations') },
      'delete': () => { return import(/* webpackChunkName: "agenda-action-event-delete" */ '#/plugin/agenda/actions/agenda_event/delete') }
    },
    agenda_task: {
      'open': () => { return import(/* webpackChunkName: "agenda-action-task-open" */ '#/plugin/agenda/actions/agenda_task/open') },
      'edit': () => { return import(/* webpackChunkName: "agenda-action-task-edit" */ '#/plugin/agenda/actions/agenda_task/edit') },
      'delete': () => { return import(/* webpackChunkName: "agenda-action-task-delete" */ '#/plugin/agenda/actions/agenda_task/delete') },
      'mark-done': () => { return import(/* webpackChunkName: "agenda-action-task-mark-done" */ '#/plugin/agenda/actions/agenda_task/mark-done') },
      'mark-todo': () => { return import(/* webpackChunkName: "agenda-action-task-mark-todo" */ '#/plugin/agenda/actions/agenda_task/mark-todo') }
    }
  }
})
