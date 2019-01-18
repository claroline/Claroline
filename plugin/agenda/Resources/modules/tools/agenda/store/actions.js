import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'


export const AGENDA_UPDATE_FILTER_TYPE = 'AGENDA_UPDATE_FILTER_TYPE'
export const AGENDA_UPDATE_FILTER_WORKSPACE = 'AGENDA_UPDATE_FILTER_WORKSPACE'
export const AGENDA_RELOAD = 'AGENDA_RELOAD'

export const actions = {}

actions.updateFilterType = makeActionCreator(AGENDA_UPDATE_FILTER_TYPE, 'filters')
actions.updateFilterWorkspace = makeActionCreator(AGENDA_UPDATE_FILTER_WORKSPACE, 'filters')

//calendarElement is required to refresh the calendar since it's outside react
actions.create = (event, workspace, calendarRef) => ({
  [API_REQUEST]: {
    url: ['apiv2_event_create'],
    request: {
      body: JSON.stringify(Object.assign({}, event, {workspace})),
      method: 'POST'
    },
    success: (data) => {
      calendarRef.fullCalendar('renderEvent', data)
    }
  }
})

actions.update = (event, calendarRef) => ({
  [API_REQUEST]: {
    url: ['apiv2_event_update', {id: event.id}],
    request: {
      body: JSON.stringify(event),
      method: 'PUT'
    },
    success: (data) => {
      calendarRef.fullCalendar('removeEvents', data.id)
      calendarRef.fullCalendar('renderEvent', data)
    }
  }
})

actions.delete = (event, calendarRef) => ({
  [API_REQUEST]: {
    url: ['apiv2_event_delete_bulk', {ids: [event.id]}],
    request: {
      method: 'DELETE'
    },
    success: () => {
      calendarRef.fullCalendar('removeEvents', event.id)
    }
  }
})

actions.import = (data, workspace = null, calendarRef) => ({
  [API_REQUEST]: {
    url: ['apiv2_event_import'],
    request: {
      body :JSON.stringify({
        file: { id: data.file.id },
        workspace: { id: workspace.id || null }
      }),
      method: 'POST'
    },
    success: (events) => {
      calendarRef.fullCalendar('addEventSource', events)
      calendarRef.fullCalendar('refetchEvents')
    }
  }
})
