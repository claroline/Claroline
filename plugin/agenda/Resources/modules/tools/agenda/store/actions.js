import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST, url} from '#/main/app/api'
import {getApiFormat} from '#/main/app/intl/date'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/plugin/agenda/tools/agenda/store/selectors'

export const AGENDA_CHANGE_TYPES = 'AGENDA_CHANGE_TYPES'
export const AGENDA_CHANGE_VIEW  = 'AGENDA_CHANGE_VIEW'
export const AGENDA_LOAD_EVENTS  = 'AGENDA_LOAD_EVENTS'
export const AGENDA_SET_LOADED   = 'AGENDA_SET_LOADED'

export const actions = {}

actions.changeTypes = makeActionCreator(AGENDA_CHANGE_TYPES, 'types')
actions.changeView = makeActionCreator(AGENDA_CHANGE_VIEW, 'view', 'referenceDate')
actions.setLoaded = makeActionCreator(AGENDA_SET_LOADED, 'loaded')

actions.load = makeActionCreator(AGENDA_LOAD_EVENTS, 'events')
actions.fetch = (rangeDates) => (dispatch, getState) => {
  const filters = {
    types: selectors.types(getState())
  }

  const contextId = toolSelectors.contextId(getState())
  if (contextId) {
    filters.workspaces = [contextId]
  }

  return dispatch({
    [API_REQUEST]: {
      url: url(['apiv2_event_list'], {
        start: rangeDates[0].format(getApiFormat()),
        end: rangeDates[1].format(getApiFormat()),
        filters: filters
      }),
      success: (response, dispatch) => dispatch(actions.load(response))
    }
  })
}

actions.delete = (event) => ({
  [API_REQUEST]: {
    url: ['apiv2_event_delete_bulk', {ids: [event.id]}],
    request: {
      method: 'DELETE'
    },
    success: (response, dispatch) => dispatch(actions.setLoaded(false))
  }
})

actions.markDone = (event) => ({
  [API_REQUEST]: {
    url: ['apiv2_task_mark_done', {ids: [event.id]}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.setLoaded(false))
  }
})

actions.markTodo = (event) => ({
  [API_REQUEST]: {
    url: ['apiv2_task_mark_todo', {ids: [event.id]}],
    request: {
      method: 'PUT'
    },
    success: (response, dispatch) => dispatch(actions.setLoaded(false))
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
