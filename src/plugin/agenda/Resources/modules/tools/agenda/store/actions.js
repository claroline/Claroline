import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST, url} from '#/main/app/api'
import {getApiFormat} from '#/main/app/intl/date'

import {selectors} from '#/plugin/agenda/tools/agenda/store/selectors'

export const AGENDA_CHANGE_TYPES = 'AGENDA_CHANGE_TYPES'
export const AGENDA_CHANGE_VIEW  = 'AGENDA_CHANGE_VIEW'
export const AGENDA_LOAD_EVENT   = 'AGENDA_LOAD_EVENT'
export const AGENDA_LOAD_EVENTS  = 'AGENDA_LOAD_EVENTS'
export const AGENDA_ADD_PLANNING = 'AGENDA_ADD_PLANNING'
export const AGENDA_REMOVE_PLANNING = 'AGENDA_REMOVE_PLANNING'
export const AGENDA_TOGGLE_PLANNING = 'AGENDA_TOGGLE_PLANNING'
export const AGENDA_CHANGE_PLANNING_COLOR = 'AGENDA_CHANGE_PLANNING_COLOR'
export const AGENDA_FORCE_PLANNING = 'AGENDA_FORCE_PLANNING'
export const AGENDA_SET_PLANNING_LOADED = 'AGENDA_SET_PLANNING_LOADED'

export const actions = {}

actions.changeTypes = makeActionCreator(AGENDA_CHANGE_TYPES, 'types')
actions.changeView = makeActionCreator(AGENDA_CHANGE_VIEW, 'view', 'referenceDate')

actions.addPlanning = makeActionCreator(AGENDA_ADD_PLANNING, 'id', 'name')
actions.removePlanning = makeActionCreator(AGENDA_REMOVE_PLANNING, 'id')
actions.togglePlanning = makeActionCreator(AGENDA_TOGGLE_PLANNING, 'id')
actions.forcePlanning = makeActionCreator(AGENDA_FORCE_PLANNING, 'id')
actions.setPlanningLoaded = makeActionCreator(AGENDA_SET_PLANNING_LOADED, 'id', 'loaded')
actions.changePlanningColor = makeActionCreator(AGENDA_CHANGE_PLANNING_COLOR, 'id', 'color')

actions.reload = (event, all = false) => (dispatch, getState) => {
  // TODO : only reload if event is created/updated in the current range
  if (!all) {
    const planningsToRefresh = selectors.eventPlannings(getState(), event.id)

    planningsToRefresh.map(planning => dispatch(actions.setPlanningLoaded(planning, false)))
  } else {
    // this is for creation, we don't know in which planning the event should appear so we reload all
    // this may be improved to avoid unwanted reloades
    const allPlannings = selectors.plannings(getState())

    allPlannings.map(planning => dispatch(actions.setPlanningLoaded(planning.id, false)))
  }
}

actions.load = makeActionCreator(AGENDA_LOAD_EVENTS, 'planningId', 'events')
actions.fetch = (rangeDates, force = false) => (dispatch, getState) => {
  const filters = {
    types: selectors.types(getState())
  }

  // we do a request for each planning to avoid a long big request
  const plannings = selectors.plannings(getState())
  return Promise.all(
    plannings.map(planning => {
      if (!planning.loaded || force) {
        // get results for this planning
        filters.planning = planning.id

        return dispatch({
          [API_REQUEST]: {
            url: url(['apiv2_planned_object_list'], {
              start: rangeDates[0].format(getApiFormat()),
              end: rangeDates[1].format(getApiFormat()),
              filters: filters
            }),
            success: (response) => dispatch(actions.load(planning.id, response.data))
          }
        })
      }
    })
  )
}

actions.loadEvent = makeActionCreator(AGENDA_LOAD_EVENT, 'event')
actions.get = (eventId) => (dispatch, getState) => {
  const currentEvent = selectors.currentEvent(getState())

  if (!currentEvent || currentEvent.id !== eventId) {
    return dispatch({
      [API_REQUEST]: {
        url: url(['apiv2_planned_object_get', {id: eventId}]),
        silent: true,
        before: () => dispatch(actions.loadEvent(null)),
        success: (response) => dispatch(actions.loadEvent(response))
      }
    })
  }
}
