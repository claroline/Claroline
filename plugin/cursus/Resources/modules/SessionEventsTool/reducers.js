import cloneDeep from 'lodash/cloneDeep'
import {makeReducer, combineReducers} from '#/main/core/utilities/redux'
import {VIEW_USER} from './enums'
import {makeListReducer} from '#/main/core/layout/list/reducer'
import {reducer as paginationReducer} from '#/main/core/layout/pagination/reducer'
import {
  SESSION_EVENTS_LOAD,
  SESSION_EVENT_LOAD,
  SESSION_EVENT_ADD,
  SESSION_EVENTS_ADD,
  SESSION_EVENT_UPDATE,
  CURRENT_EVENT_RESET,
  CURRENT_EVENT_ADD_PARTICIPANTS,
  CURRENT_EVENT_REMOVE_PARTICIPANTS,
  CURRENT_EVENT_UPDATE_PARTICIPANT,
  UPDATE_VIEW_MODE,
  CURRENT_ERROR_RESET,
  CURRENT_ERROR_UPDATE,
  EVENTS_USERS_ADD,
  EVENT_COMMENTS_RESET,
  EVENT_COMMENTS_LOAD,
  EVENT_COMMENT_ADD,
  EVENT_COMMENT_UPDATE,
  EVENT_COMMENT_REMOVE,
  LOCATIONS_LOAD,
  LOCATIONS_LOADED_UPDATE,
  TEACHERS_LOAD,
  TEACHERS_LOADED_UPDATE,
  SET_EVENTS_RESET,
  SET_EVENTS_LOAD,
  SET_EVENTS_USERS_ADD
} from './actions'

const initialState = {
  workspaceId: null,
  canEdit: 0,
  disableRegistration: 1,
  sessions: {},
  sessionId: null,
  currentEvent: {
    data: {},
    participants: []
  },
  events: {},
  eventsUsers: {},
  viewMode: VIEW_USER,
  eventForm: {
    id: null,
    name: null,
    description: null,
    startDate: null,
    endDate: null,
    registrationType: 0,
    maxUsers: null,
    locationExtra: null
  },
  currentError: null,
  eventComments: [],
  locations: [],
  locationsLoaded: false,
  teachers: [],
  teachersLoaded: false,
  setEvents: {
    events: [],
    registrations: {},
    nbRegistrations: 0
  }
}

const mainReducers = {}

const currentEventReducers = {
  [CURRENT_EVENT_RESET]: () => initialState['currentEvent'],
  [SESSION_EVENT_LOAD]: (state, action) => {
    return action.sessionEvent
  },
  [SESSION_EVENT_UPDATE]: (state, action) => {
    return state.data.id === action.sessionEvent.id ?
      Object.assign({}, state, {data: action.sessionEvent}) :
      state
  },
  [CURRENT_EVENT_ADD_PARTICIPANTS]: (state, action) => {
    const participants = cloneDeep(state.participants)
    action.sessionEventUsers.forEach(seu => participants.push(seu))

    return Object.assign({}, state, {participants: participants})
  },
  [CURRENT_EVENT_REMOVE_PARTICIPANTS]: (state, action) => {
    const participants = cloneDeep(state.participants)
    action.sessionEventUsersIds.forEach(id => {
      const index = participants.findIndex(p => p.id === id)

      if (index > -1) {
        participants.splice(index, 1)
      }
    })

    return Object.assign({}, state, {participants: participants})
  },
  [CURRENT_EVENT_UPDATE_PARTICIPANT]: (state, action) => {
    const participants = state.participants.map(p => {
      if (p.id === action.sessionEventUser.id) {
        return action.sessionEventUser
      } else {
        return p
      }
    })

    return Object.assign({}, state, {participants: participants})
  }
}

const eventsReducers = {
  [SESSION_EVENTS_LOAD]: (state, action) => {
    return {
      data: action.sessionEvents,
      totalResults: action.total
    }
  },
  [SESSION_EVENT_ADD]: (state, action) => {
    const events = cloneDeep(state.data)
    events.push(action.sessionEvent)

    return {
      data: events,
      totalResults: state.totalResults + 1
    }
  },
  [SESSION_EVENTS_ADD]: (state, action) => {
    const events = cloneDeep(state.data)
    action.sessionEvents.forEach(se => events.push(se))

    return {
      data: events,
      totalResults: state.totalResults + action.sessionEvents.length
    }
  },
  [SESSION_EVENT_UPDATE]: (state, action) => {
    const events = state.data.map((event) => {
      if (event.id === action.sessionEvent.id) {
        return action.sessionEvent
      } else {
        return event
      }
    })

    return {
      data: events,
      totalResults: state.totalResults
    }
  }
}

const viewReducers = {
  [UPDATE_VIEW_MODE]: (state, action) => {
    return action.mode
  }
}

const currentErrorReducers = {
  [CURRENT_ERROR_RESET]: () => initialState['currentError'],
  [CURRENT_ERROR_UPDATE]: (state, action) => {
    return action.error
  }
}

const eventsUsersReducers = {
  [EVENTS_USERS_ADD]: (state, action) => {
    const eventsUsers = cloneDeep(state)
    action.sessionEventUsers.forEach(seu => eventsUsers[seu.sessionEvent.id] = seu)

    return eventsUsers
  }
}

const eventCommentsReducers = {
  [EVENT_COMMENTS_RESET]: () => initialState['eventComments'],
  [EVENT_COMMENTS_LOAD]: (state, action) => {
    return action.eventComments
  },
  [EVENT_COMMENT_ADD]: (state, action) => {
    const eventComments = cloneDeep(state)
    eventComments.push(action.eventComment)

    return eventComments
  },
  [EVENT_COMMENT_UPDATE]: (state, action) => {
    const eventComments = state.map((comment) => {
      if (comment.id === action.eventComment.id) {
        return action.eventComment
      } else {
        return comment
      }
    })

    return eventComments
  },
  [EVENT_COMMENT_REMOVE]: (state, action) => {
    const eventComments = cloneDeep(state)
    const index = eventComments.findIndex(ec => ec.id === action.eventCommentId)

    if (index > -1) {
      eventComments.splice(index, 1)
    }

    return eventComments
  }
}

const locationsReducers = {
  [LOCATIONS_LOAD]: (state, action) => action.locations
}

const locationsLoadedReducers = {
  [LOCATIONS_LOADED_UPDATE]: (state, action) => action.loaded
}

const teachersReducers = {
  [TEACHERS_LOAD]: (state, action) => action.teachers
}

const teachersLoadedReducers = {
  [TEACHERS_LOADED_UPDATE]: (state, action) => action.loaded
}

const setEventsReducers = {
  [SET_EVENTS_RESET]: () => initialState['setEvents'],
  [SET_EVENTS_LOAD]: (state, action) => {
    return {
      events: action.events,
      registrations: action.registrations,
      nbRegistrations: Object.keys(action.registrations).length
    }
  },
  [SET_EVENTS_USERS_ADD]: (state, action) => {
    const registrations = cloneDeep(state.registrations)
    action.sessionEventUsers.forEach(seu => {
      registrations[seu.sessionEvent.id] = seu
    })

    return {
      events: state.events,
      registrations: registrations,
      nbRegistrations: state.nbRegistrations + action.sessionEventUsers.length
    }
  }
}

export const reducers = combineReducers({
  workspaceId: makeReducer(initialState['workspaceId'], mainReducers),
  canEdit: makeReducer(initialState['canEdit'], mainReducers),
  disableRegistration: makeReducer(initialState['disableRegistration'], mainReducers),
  sessions: makeReducer(initialState['sessions'], mainReducers),
  sessionId: makeReducer(initialState['sessionId'], mainReducers),
  currentEvent: makeReducer(initialState['currentEvent'], currentEventReducers),
  events: makeReducer(initialState['events'], eventsReducers),
  eventsUsers: makeReducer(initialState['eventsUsers'], eventsUsersReducers),
  viewMode: makeReducer(initialState['viewMode'], viewReducers),
  currentError: makeReducer(initialState['currentError'], currentErrorReducers),
  eventComments: makeReducer(initialState['eventComments'], eventCommentsReducers),
  locations: makeReducer(initialState['locations'], locationsReducers),
  locationsLoaded: makeReducer(initialState['locationsLoaded'], locationsLoadedReducers),
  teachers: makeReducer(initialState['teachers'], teachersReducers),
  teachersLoaded: makeReducer(initialState['teachersLoaded'], teachersLoadedReducers),
  setEvents: makeReducer(initialState['setEvents'], setEventsReducers),
  list: makeListReducer(),
  pagination: paginationReducer
})