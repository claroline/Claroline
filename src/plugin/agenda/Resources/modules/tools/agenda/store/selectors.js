import {createSelector} from 'reselect'
import moment from 'moment'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

const STORE_NAME = 'agenda'

const store = (state) => state[STORE_NAME]

const view = createSelector(
  [store],
  (store) => store.view
)

const referenceDateStr = createSelector(
  [store],
  (store) => store.referenceDate
)

const referenceDate = createSelector(
  [referenceDateStr],
  (referenceDateStr) => moment(referenceDateStr)
)

const types = createSelector(
  [store],
  (store) => store.types
)

const plannings = createSelector(
  [store],
  (store) => store.plannings
)

const loaded = createSelector(
  [plannings],
  (plannings) => -1 === plannings.findIndex(planning => !planning.loaded)
)

const planningEvents = createSelector(
  [store],
  (store) => store.events
)

const events = createSelector(
  [plannings, planningEvents],
  (plannings, planningEvents) => {
    let events = []

    plannings.map(planning => {
      if (planning.displayed && !isEmpty(planningEvents[planning.id])) {
        if (planning.color) {
          // override events color by the one chosen for the whole planning
          events = events.concat(planningEvents[planning.id].map(event => merge({}, event, {
            display: {color: planning.color}
          })))
        } else {
          events = events.concat(planningEvents[planning.id])
        }
      }
    })

    return events
  }
)

const currentEvent = createSelector(
  [store],
  (store) => store.current
)

// retrieves all the plannings which contains the event
const eventPlannings = (state, eventId) => {
  const events = planningEvents(state)

  return Object.keys(events)
    .filter((planningId) => -1 !== events[planningId].findIndex(event => event.id === eventId))
}

export const selectors = {
  STORE_NAME,

  view,
  referenceDateStr,
  referenceDate,
  types,
  plannings,

  loaded,
  events,
  currentEvent,
  eventPlannings
}
