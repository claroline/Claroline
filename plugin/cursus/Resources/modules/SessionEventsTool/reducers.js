import cloneDeep from 'lodash/cloneDeep'
import {makeReducer, combineReducers} from '#/main/core/utilities/redux'
import {VIEW_USER} from './enums'
import {makeListReducer} from '#/main/core/layout/list/reducer'
import {reducer as paginationReducer} from '#/main/core/layout/pagination/reducer'
import {
  SESSION_EVENTS_LOAD,
  SESSION_EVENT_LOAD,
  SESSION_EVENT_ADD,
  SESSION_EVENT_UPDATE,
  CURRENT_EVENT_RESET,
  CURRENT_EVENT_ADD_PARTICIPANTS,
  CURRENT_EVENT_REMOVE_PARTICIPANTS,
  EVENT_FORM_RESET,
  EVENT_FORM_UPDATE,
  EVENT_FORM_LOAD,
  UPDATE_VIEW_MODE,
  CURRENT_ERROR_RESET,
  CURRENT_ERROR_UPDATE,
  EVENTS_USERS_ADD
} from './actions'

const initialState = {
  workspaceId: null,
  canEdit: 0,
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
    maxUsers: null
  },
  currentError: null
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

const eventFormReducers = {
  [EVENT_FORM_RESET]: () => initialState['eventForm'],
  [EVENT_FORM_UPDATE]: (event, action) => {
    const newEvent = cloneDeep(event)
    newEvent[action.property] = action.value

    return newEvent
  },
  [EVENT_FORM_LOAD]: (state, action) => {
    return action.event
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

export const reducers = combineReducers({
  workspaceId: makeReducer(initialState['workspaceId'], mainReducers),
  canEdit: makeReducer(initialState['canEdit'], mainReducers),
  sessions: makeReducer(initialState['sessions'], mainReducers),
  sessionId: makeReducer(initialState['sessionId'], mainReducers),
  currentEvent: makeReducer(initialState['currentEvent'], currentEventReducers),
  events: makeReducer(initialState['events'], eventsReducers),
  eventsUsers: makeReducer(initialState['eventsUsers'], eventsUsersReducers),
  viewMode: makeReducer(initialState['viewMode'], viewReducers),
  eventForm: makeReducer(initialState['eventForm'], eventFormReducers),
  currentError: makeReducer(initialState['currentError'], currentErrorReducers),
  list: makeListReducer(),
  pagination: paginationReducer
})