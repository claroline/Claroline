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
export const SESSION_EVENTS_ADD = 'SESSION_EVENTS_ADD'
export const SESSION_EVENT_UPDATE = 'SESSION_EVENT_UPDATE'
export const CURRENT_EVENT_RESET = 'CURRENT_EVENT_RESET'
export const CURRENT_EVENT_ADD_PARTICIPANTS = 'CURRENT_EVENT_ADD_PARTICIPANTS'
export const CURRENT_EVENT_REMOVE_PARTICIPANTS = 'CURRENT_EVENT_REMOVE_PARTICIPANTS'
export const CURRENT_EVENT_UPDATE_PARTICIPANT = 'CURRENT_EVENT_UPDATE_PARTICIPANT'
export const UPDATE_VIEW_MODE = 'UPDATE_VIEW_MODE'
export const CURRENT_ERROR_RESET = 'CURRENT_ERROR_RESET'
export const CURRENT_ERROR_UPDATE = 'CURRENT_ERROR_UPDATE'
export const EVENTS_USERS_ADD = 'EVENTS_USERS_ADD'
export const EVENT_COMMENTS_RESET = 'EVENT_COMMENTS_RESET'
export const EVENT_COMMENTS_LOAD = 'EVENT_COMMENTS_LOAD'
export const EVENT_COMMENT_ADD = 'EVENT_COMMENT_ADD'
export const EVENT_COMMENT_UPDATE = 'EVENT_COMMENT_UPDATE'
export const EVENT_COMMENT_REMOVE = 'EVENT_COMMENT_REMOVE'
export const LOCATIONS_LOAD = 'LOCATIONS_LOAD'
export const LOCATIONS_LOADED_UPDATE = 'LOCATIONS_LOADED_UPDATE'
export const TEACHERS_LOAD = 'TEACHERS_LOAD'
export const TEACHERS_LOADED_UPDATE = 'TEACHERS_LOADED_UPDATE'
export const SET_EVENTS_RESET = 'SET_EVENTS_RESET'
export const SET_EVENTS_LOAD = 'SET_EVENTS_LOAD'
export const SET_EVENTS_USERS_ADD = 'SET_EVENTS_USERS_ADD'

export const actions = {}

actions.loadSessionEvents = makeActionCreator(SESSION_EVENTS_LOAD, 'sessionEvents', 'total')

actions.addSessionEvent = makeActionCreator(SESSION_EVENT_ADD, 'sessionEvent')

actions.addSessionEvents = makeActionCreator(SESSION_EVENTS_ADD, 'sessionEvents')

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
    if (eventData['location'] !== undefined) {
      formData.append('location', eventData['location'])
    }
    if (eventData['locationExtra'] !== undefined) {
      formData.append('locationExtra', eventData['locationExtra'])
    }
    if (eventData['teachers'] !== undefined) {
      formData.append('teachers', eventData['teachers'])
    }
    if (eventData['eventSet'] !== undefined) {
      formData.append('eventSet', eventData['eventSet'])
    }
    const type = eventData['isAgendaEvent'] ? 1 : 0
    formData.append('type', type)

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
    if (eventData['location'] !== undefined) {
      formData.append('location', eventData['location'])
    }
    if (eventData['locationExtra'] !== undefined) {
      formData.append('locationExtra', eventData['locationExtra'])
    }
    if (eventData['teachers'] !== undefined) {
      formData.append('teachers', eventData['teachers'])
    }
    if (eventData['eventSet'] !== undefined) {
      formData.append('eventSet', eventData['eventSet'])
    }
    const type = eventData['isAgendaEvent'] ? 1 : 0
    formData.append('type', type)

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

actions.repeatSessionEvent = (sessionEventId, repeatEventData) => {
  return (dispatch) => {
    const formData = new FormData()

    if (repeatEventData['monday'] !== undefined) {
      formData.append('monday', repeatEventData['monday'] ? 1 : 0)
    }
    if (repeatEventData['tuesday'] !== undefined) {
      formData.append('tuesday', repeatEventData['tuesday'] ? 1 : 0)
    }
    if (repeatEventData['wednesday'] !== undefined) {
      formData.append('wednesday', repeatEventData['wednesday'] ? 1 : 0)
    }
    if (repeatEventData['thursday'] !== undefined) {
      formData.append('thursday', repeatEventData['thursday'] ? 1 : 0)
    }
    if (repeatEventData['friday'] !== undefined) {
      formData.append('friday', repeatEventData['friday'] ? 1 : 0)
    }
    if (repeatEventData['saturday'] !== undefined) {
      formData.append('saturday', repeatEventData['saturday'] ? 1 : 0)
    }
    if (repeatEventData['sunday'] !== undefined) {
      formData.append('sunday', repeatEventData['sunday'] ? 1 : 0)
    }
    if (repeatEventData['until'] !== undefined) {
      formData.append('until', repeatEventData['until'])
    }
    if (repeatEventData['duration'] !== undefined) {
      formData.append('duration', repeatEventData['duration'])
    }
    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_repeat', {sessionEvent: sessionEventId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.addSessionEvents(JSON.parse(data)))
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

actions.acceptSessionEventUser = (sessionEventUserId) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_user_accept', {sessionEventUser: sessionEventUserId}),
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      if (data['status'] === 'success') {
        dispatch(actions.updateParticipant(JSON.parse(data['data'])))
      } else {
        dispatch(actions.updateCurrentError(data['data']))
      }
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

actions.selfRegisterToSessionEvent = (sessionEventId, addInSet = false) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_self_register', {sessionEvent: sessionEventId}),
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => {
      const sessionEventUsers = JSON.parse(data['sessionEventUsers'])
      dispatch(actions.addEventsUsers(sessionEventUsers))

      if (addInSet) {
        dispatch(actions.addSetEventsUsers(sessionEventUsers))
      }
    }
  }
})

actions.getAllLocations = () => (dispatch, getState) => {
  const state = getState()
  const workspaceId = state.workspaceId
  const loaded = state.locationsLoaded
  const url = generateUrl('claro_cursus_locations_retrieve', {workspace: workspaceId})

  if (!loaded) {
    dispatch({
      [REQUEST_SEND]: {
        url: url,
        request: {
          method: 'GET'
        },
        success: (data, dispatch) => {
          const locations = JSON.parse(data)
          dispatch(actions.loadLocations(locations))
          dispatch(actions.updateLocationsLoaded(true))
        }
      }
    })
  }
}

actions.getSessionTeachers = () => (dispatch, getState) => {
  const state = getState()
  const sessionId = state.sessionId
  const loaded = state.teachersLoaded
  const url = generateUrl('claro_cursus_session_teachers_retrieve', {session: sessionId})

  if (sessionId && !loaded) {
    dispatch({
      [REQUEST_SEND]: {
        url: url,
        request: {
          method: 'GET'
        },
        success: (data, dispatch) => {
          const teachers = JSON.parse(data)
          dispatch(actions.loadTeachers(teachers))
          dispatch(actions.updateTeachersLoaded(true))
        }
      }
    })
  }
}

actions.getEventComments = (sessionEventId) => (dispatch) => {
  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_cursus_session_event_comments_retrieve', {sessionEvent: sessionEventId}),
      request: {
        method: 'GET'
      },
      success: (data, dispatch) => {
        dispatch(actions.loadEventComments(JSON.parse(data)))
      }
    }
  })
}

actions.createEventComment = (eventId, content) => (dispatch) => {
  if (content) {
    const formData = new FormData()
    formData.append('content', content)

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_comment_create', {sessionEvent: eventId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.addEventComment(JSON.parse(data)))
        }
      }
    })
  }
}

actions.editEventComment = (eventCommentId, content) => (dispatch) => {
  if (content) {
    const formData = new FormData()
    formData.append('content', content)

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_comment_edit', {sessionEventComment: eventCommentId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.updateEventComment(JSON.parse(data)))
        }
      }
    })
  }
}

actions.deleteEventComment = (eventCommentId) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_comment_delete', {sessionEventComment: eventCommentId}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.removeEventComment(eventCommentId))
    }
  }
})

actions.editEventSet = (eventSetId, eventSetData) => {
  return (dispatch) => {
    const formData = new FormData()

    if (eventSetData['name'] !== undefined) {
      formData.append('name', eventSetData['name'])
    }
    if (eventSetData['limit'] !== undefined) {
      formData.append('limit', eventSetData['limit'])
    }

    dispatch({
      [REQUEST_SEND]: {
        url: generateUrl('claro_cursus_session_event_set_edit', {sessionEventSet: eventSetId}),
        request: {
          method: 'POST',
          body: formData
        },
        success: (data, dispatch) => {
          dispatch(actions.fetchSessionEvents())
        }
      }
    })
  }
}

actions.deleteEventSet = (eventSetId) => ({
  [REQUEST_SEND]: {
    url: generateUrl('claro_cursus_session_event_set_delete', {sessionEventSet: eventSetId}),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => {
      dispatch(actions.fetchSessionEvents())
    }
  }
})

actions.getSetEvents = (sessionEventSetId) => (dispatch) => {
  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_cursus_session_event_set_events_retrieve', {sessionEventSet: sessionEventSetId}),
      request: {
        method: 'GET'
      },
      success: (data, dispatch) => {
        dispatch(actions.loadSetEvents(JSON.parse(data.events), JSON.parse(data.registrations)))
      }
    }
  })
}

actions.resetCurrentSessionEvent = makeActionCreator(CURRENT_EVENT_RESET)

actions.addParticipants = makeActionCreator(CURRENT_EVENT_ADD_PARTICIPANTS, 'sessionEventUsers')

actions.removeParticipants = makeActionCreator(CURRENT_EVENT_REMOVE_PARTICIPANTS, 'sessionEventUsersIds')

actions.updateParticipant = makeActionCreator(CURRENT_EVENT_UPDATE_PARTICIPANT, 'sessionEventUser')

actions.loadSessionEvent = makeActionCreator(SESSION_EVENT_LOAD, 'sessionEvent')

actions.updateViewMode = makeActionCreator(UPDATE_VIEW_MODE, 'mode')

actions.resetCurrentError = makeActionCreator(CURRENT_ERROR_RESET)

actions.updateCurrentError = makeActionCreator(CURRENT_ERROR_UPDATE, 'error')

actions.addEventsUsers = makeActionCreator(EVENTS_USERS_ADD, 'sessionEventUsers')

actions.resetEventComments = makeActionCreator(EVENT_COMMENTS_RESET)

actions.loadEventComments = makeActionCreator(EVENT_COMMENTS_LOAD, 'eventComments')

actions.addEventComment = makeActionCreator(EVENT_COMMENT_ADD, 'eventComment')

actions.updateEventComment = makeActionCreator(EVENT_COMMENT_UPDATE, 'eventComment')

actions.removeEventComment = makeActionCreator(EVENT_COMMENT_REMOVE, 'eventCommentId')

actions.loadLocations = makeActionCreator(LOCATIONS_LOAD, 'locations')

actions.updateLocationsLoaded = makeActionCreator(LOCATIONS_LOADED_UPDATE, 'loaded')

actions.loadTeachers = makeActionCreator(TEACHERS_LOAD, 'teachers')

actions.updateTeachersLoaded = makeActionCreator(TEACHERS_LOADED_UPDATE, 'loaded')

actions.resetSetEvents = makeActionCreator(SET_EVENTS_RESET)

actions.loadSetEvents = makeActionCreator(SET_EVENTS_LOAD, 'events', 'registrations')

actions.addSetEventsUsers = makeActionCreator(SET_EVENTS_USERS_ADD, 'sessionEventUsers')

const getQueryString = (idsList) => '?' + idsList.map(id => 'ids[]='+id).join('&')