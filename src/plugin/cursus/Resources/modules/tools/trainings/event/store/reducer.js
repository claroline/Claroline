import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/trainings/event/store/selectors'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {constants} from '#/plugin/cursus/constants'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store'

export const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME+'.list', {
    sortBy: {property: 'plannedObject.startDate', direction: -1},
    filters: {filters: [{property: 'plannedObject.status', value: 'not_ended'}]}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),
  participants: makeListReducer(selectors.STORE_NAME+'.participants', {
    sortBy: {property: 'date', direction: -1},
    filters: {filters: [
      {property: 'type', value: constants.LEARNER_TYPE, locked: true, hidden: true},
      {property: 'event.status', value: 'not_ended'}
    ]}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),
  tutors: makeListReducer(selectors.STORE_NAME+'.tutors', {
    sortBy: {property: 'date', direction: -1},
    filters: {filters: [
      {property: 'type', value: constants.TEACHER_TYPE, locked: true, hidden: true},
      {property: 'event.status', value: 'not_ended'}
    ]}
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  })
})
