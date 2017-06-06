import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'
import {REQUEST_SEND} from '#/main/core/api/actions'
import {actions as listActions} from '#/main/core/layout/list/actions'
import {select as listSelect} from '#/main/core/layout/list/selectors'
import {actions as paginationActions} from '#/main/core/layout/pagination/actions'
import {select as paginationSelect} from '#/main/core/layout/pagination/selectors'
import {trans} from '#/main/core/translation'
import {VIEW_MANAGER, VIEW_USER, VIEW_EVENT} from './enums'

export const SESSION_EVENTS_LOAD = 'SESSION_EVENTS_LOAD'
export const SESSION_EVENT_LOAD = 'SESSION_EVENT_LOAD'
export const SESSION_EVENT_ADD = 'SESSION_EVENT_ADD'
export const SESSION_EVENT_UPDATE = 'SESSION_EVENT_UPDATE'
export const CURRENT_EVENT_RESET = 'CURRENT_EVENT_RESET'
export const CURRENT_EVENT_ADD_PARTICIPANTS = 'CURRENT_EVENT_ADD_PARTICIPANTS'
export const CURRENT_EVENT_REMOVE_PARTICIPANTS = 'CURRENT_EVENT_REMOVE_PARTICIPANTS'
export const EVENT_FORM_RESET = 'EVENT_FORM_RESET'
export const EVENT_FORM_UPDATE = 'EVENT_FORM_UPDATE'
export const EVENT_FORM_LOAD = 'EVENT_FORM_LOAD'
export const UPDATE_VIEW_MODE = 'UPDATE_VIEW_MODE'
export const CURRENT_ERROR_RESET = 'CURRENT_ERROR_RESET'
export const CURRENT_ERROR_UPDATE = 'CURRENT_ERROR_UPDATE'
export const EVENTS_USERS_ADD = 'EVENTS_USERS_ADD'

export const actions = {}

actions.loadSessionEvents = makeActionCreator(SESSION_EVENTS_LOAD, 'sessionEvents', 'total')

actions.addSessionEvent = makeActionCreator(SESSION_EVENT_ADD, 'sessionEvent')

actions.updateSessionEvent = makeActionCreator(SESSION_EVENT_UPDATE, 'sessionEvent')

actions.deleteSessionEvent = (workspaceId, sessionEventId) => ({
  [REQUEST_SEND] : {
    url: generateUrl('claro_cursus_session_event_delete', {workspace: workspaceId, sessionEvent: sessionEventId}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(paginationActions.changePage(0))
      dispatch(actions.fetchSessionEvents())
    }
  }
})

actions.deleteSessionEvents = (workspaceId, sessionEvents) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_events_delete', {workspace: workspaceId}) + getQueryString(sessionEvents),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(paginationActions.changePage(0))
      dispatch(actions.fetchSessionEvents())
    }
  }
})

actions.createSessionEvent = (sessionId, eventData) => {
  return (dispatch) => {
    const formData = new FormData()

    if (eventData['name'] !== undefined) {
      formData.append('name', eventData['name'])
    }
    if (eventData['description'] !== undefined) {
      formData.append('description', eventData['description'])
    }
    if (eventData['startDate'] !== undefined) {
      formData.append('startDate', eventData['startDate'])
    }
    if (eventData['endDate'] !== undefined) {
      formData.append('endDate', eventData['endDate'])
    }
    if (eventData['registrationType'] !== undefined) {
      formData.append('registrationType', eventData['registrationType'])
    }
    if (eventData['maxUsers'] !== undefined) {
      formData.append('maxUsers', eventData['maxUsers'])
    }

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_create', {session: sessionId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.addSessionEvent(JSON.parse(data)))
        }
      }
    })
  }
}

actions.editSessionEvent = (eventId, eventData) => {
  return (dispatch) => {
    const formData = new FormData()

    if (eventData['name'] !== undefined) {
      formData.append('name', eventData['name'])
    }
    if (eventData['description'] !== undefined) {
      formData.append('description', eventData['description'])
    }
    if (eventData['startDate'] !== undefined) {
      formData.append('startDate', eventData['startDate'])
    }
    if (eventData['endDate'] !== undefined) {
      formData.append('endDate', eventData['endDate'])
    }
    if (eventData['registrationType'] !== undefined) {
      formData.append('registrationType', eventData['registrationType'])
    }
    if (eventData['maxUsers'] !== undefined) {
      formData.append('maxUsers', eventData['maxUsers'])
    }

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_edit', {sessionEvent: eventId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.updateSessionEvent(JSON.parse(data)))
        }
      }
    })
  }
}

actions.fetchSessionEvents = () => (dispatch, getState) => {
  const state = getState()
  const page = paginationSelect.current(state)
  const pageSize = paginationSelect.pageSize(state)
  const url = generateUrl('claro_cursus_session_events_search', {session: state.sessionId, page: page, limit: pageSize}) + '?'

  // build queryString
  let queryString = ''

  // add filters
  const filters = listSelect.filters(state)
  if (0 < filters.length) {
    queryString += filters.map(filter => `filters[${filter.property}]=${filter.value}`).join('&')
  }

  // add sort by
  const sortBy = listSelect.sortBy(state)
  if (sortBy.property && 0 !== sortBy.direction) {
    queryString += `${0 < queryString.length ? '&':''}sortBy=${-1 === sortBy.direction ? '-':''}${sortBy.property}`
  }

  dispatch({
    [REQUEST_SEND]: {
      url: url + queryString,
      request: {
        method: 'GET'
      },
      success: (data, dispatch) => {
        dispatch(listActions.resetSelect())
        dispatch(actions.loadSessionEvents(JSON.parse(data.sessionEvents), data.total))
      }
    }
  })
}

actions.fetchSessionEvent = (sessionEventId) => {
  return (dispatch) => {
    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_fetch', {sessionEvent: sessionEventId}),
        request: {method: 'GET'},
        success: (data, dispatch) => {
          dispatch(actions.loadSessionEvent({data: JSON.parse(data['data']), participants: JSON.parse(data['participants'])}))
        }
      }
    })
  }
}

actions.registerUsersToSessionEvent = (sessionEventId, usersIds) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_users_register', {sessionEvent: sessionEventId}) + getQueryString(usersIds),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      if (data['status'] === 'failed') {
        const errorMsg = trans('registration_failed', {}, 'cursus') +
          '. ' +
          trans(
            'required_places_msg',
            {remainingPlaces: data['datas']['remainingPlaces'], requiredPlaces: data['datas']['requiredPlaces']},
            'cursus'
          )
        dispatch(actions.updateCurrentError(errorMsg))
      } else {
        const sessionEventUsers = JSON.parse(data['sessionEventUsers'])
        dispatch(actions.addParticipants(sessionEventUsers))
      }
    }
  }
})

actions.deleteSessionEventUsers = (sessionEventUsersIds) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_users_delete') + getQueryString(sessionEventUsersIds),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      const sessionEventUsersIds = []
      JSON.parse(data).forEach(seu => sessionEventUsersIds.push(seu.id))
      dispatch(actions.removeParticipants(sessionEventUsersIds))
    }
  }
})

actions.displayMainView = () => (dispatch, getState) => {
  const state = getState()
  const mode = state['canEdit'] ? VIEW_MANAGER : VIEW_USER
  dispatch(actions.updateViewMode(mode))
}

actions.displaySessionEvent = (sessionEventId) => {
  return (dispatch) => {
    dispatch(actions.fetchSessionEvent(sessionEventId))
    dispatch(actions.updateViewMode(VIEW_EVENT))
  }
}

actions.selfRegisterToSessionEvent = (sessionEventId) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_self_register', {sessionEvent: sessionEventId}),
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => {
      const sessionEventUsers = JSON.parse(data['sessionEventUsers'])
      dispatch(actions.addEventsUsers(sessionEventUsers))
    }
  }
})

actions.resetCurrentSessionEvent = makeActionCreator(CURRENT_EVENT_RESET)

actions.addParticipants = makeActionCreator(CURRENT_EVENT_ADD_PARTICIPANTS, 'sessionEventUsers')

actions.removeParticipants = makeActionCreator(CURRENT_EVENT_REMOVE_PARTICIPANTS, 'sessionEventUsersIds')

actions.loadSessionEvent = makeActionCreator(SESSION_EVENT_LOAD, 'sessionEvent')

actions.updateViewMode = makeActionCreator(UPDATE_VIEW_MODE, 'mode')

actions.resetEventForm = makeActionCreator(EVENT_FORM_RESET)

actions.updateEventForm = makeActionCreator(EVENT_FORM_UPDATE, 'property', 'value')

actions.loadEventForm = makeActionCreator(EVENT_FORM_LOAD, 'event')

actions.resetCurrentError = makeActionCreator(CURRENT_ERROR_RESET)

actions.updateCurrentError = makeActionCreator(CURRENT_ERROR_UPDATE, 'error')

actions.addEventsUsers = makeActionCreator(EVENTS_USERS_ADD, 'sessionEventUsers')

const getQueryString = (idsList) => '?' + idsList.map(id => 'ids[]='+id).join('&')