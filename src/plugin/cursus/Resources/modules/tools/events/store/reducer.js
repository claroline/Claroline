import {combineReducers} from 'redux'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/tools/events/store/selectors'

export const reducer = combineReducers({
  events: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'startDate', direction: -1},
    filters: [{property: 'status', value: 'not_ended'}]
  }),
  presences: makeListReducer(selectors.STORE_NAME+'.presences', {
    sortBy: {property: 'user', direction: 1}
  })
})
