import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {makeLogReducer} from '#/main/core/layout/logs/reducer'

import {selectors} from '#/plugin/analytics/resource/dashboard/store/selectors'

const reducer = combineReducers(makeLogReducer({}, {
  evaluations: makeListReducer(selectors.STORE_NAME + '.evaluations'),
  connections: makeListReducer(selectors.STORE_NAME + '.connections', {
    sortBy: {property: 'connectionDate', direction: -1}
  })
}, selectors.STORE_NAME+'.'))

export {
  reducer
}
