import moment from 'moment'
import cloneDeep from 'lodash/cloneDeep'

import {now, getApiFormat} from '#/main/app/intl/date'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {
  AGENDA_CHANGE_TYPES,
  AGENDA_CHANGE_VIEW,
  AGENDA_LOAD_EVENTS,
  AGENDA_LOAD_EVENT,
  AGENDA_ADD_PLANNING,
  AGENDA_REMOVE_PLANNING,
  AGENDA_TOGGLE_PLANNING,
  AGENDA_FORCE_PLANNING,
  AGENDA_CHANGE_PLANNING_COLOR,
  AGENDA_SET_PLANNING_LOADED
} from '#/plugin/agenda/tools/agenda/store/actions'

const reducer = combineReducers({
  view: makeReducer('month', {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => 'month',
    [AGENDA_CHANGE_VIEW]: (state, action) => action.view
  }),

  referenceDate: makeReducer(now(), {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => now(),
    [AGENDA_CHANGE_VIEW]: (state, action) => moment(action.referenceDate).format(getApiFormat()),
    [AGENDA_LOAD_EVENT]: (state, action) => action.event && action.event.start ? moment(action.event.start).format(getApiFormat()) : state
  }),

  types: makeReducer(['event', 'task'], {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => ['event', 'task'],
    [AGENDA_CHANGE_TYPES]: (state, action) => action.types
  }),

  plannings: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: (state, action) => action.toolData.plannings.map(planning => Object.assign(planning, {
      displayed: true
    })),
    [AGENDA_ADD_PLANNING]: (state, action) => {
      const newState = [].concat(state)

      if (-1 === state.findIndex(planning => planning.id === action.id)) {
        newState.push({
          id: action.id,
          name: action.name,
          color: null,
          displayed: true,
          loaded: false
        })
      }

      return newState
    },
    [AGENDA_REMOVE_PLANNING]: (state, action) => {
      const newState = [].concat(state)
      const pos = state.findIndex(planning => planning.id === action.id)
      if (-1 !== pos) {
        newState.splice(pos, 1)
      }

      return newState
    },
    [AGENDA_TOGGLE_PLANNING]: (state, action) => {
      const newState = cloneDeep(state)
      const pos = state.findIndex(planning => planning.id === action.id)
      if (-1 !== pos) {
        newState[pos].displayed = !newState[pos].displayed
      }

      return newState
    },
    [AGENDA_FORCE_PLANNING]: (state, action) => {
      const newState = cloneDeep(state)

      return newState.map(planning => Object.assign(planning, {
        // hide all planning except the chosen one
        displayed: action.id === planning.id
      }))
    },
    [AGENDA_SET_PLANNING_LOADED]: (state, action) => {
      const newState = cloneDeep(state)
      const pos = state.findIndex(planning => planning.id === action.id)
      if (-1 !== pos) {
        newState[pos].loaded = action.loaded
      }

      return newState
    },
    [AGENDA_CHANGE_PLANNING_COLOR]: (state, action) => {
      const newState = cloneDeep(state)
      const pos = state.findIndex(planning => planning.id === action.id)
      if (-1 !== pos) {
        newState[pos].color = action.color
      }

      return newState
    },
    [AGENDA_LOAD_EVENTS]: (state, action) => {
      const newState = cloneDeep(state)
      const pos = state.findIndex(planning => planning.id === action.planningId)
      if (-1 !== pos) {
        newState[pos].loaded = true
      }

      return newState
    },
    [AGENDA_CHANGE_TYPES]: (state) => {
      const newState = cloneDeep(state)

      return newState.map(planning => Object.assign(planning, {
        loaded: false
      }))
    },
    [AGENDA_CHANGE_VIEW]: (state) => {
      const newState = cloneDeep(state)

      return newState.map(planning => Object.assign(planning, {
        loaded: false
      }))
    }
  }),

  events: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => ({}),
    [AGENDA_LOAD_EVENTS]: (state, action) => {
      const newState = cloneDeep(state)

      newState[action.planningId] = action.events

      return newState
    }
  }),

  current: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, 'agenda')]: () => null,
    [AGENDA_LOAD_EVENT]: (state, action) => action.event
  })
})

export {
  reducer
}
