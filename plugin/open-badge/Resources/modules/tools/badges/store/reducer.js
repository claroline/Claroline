import {Badge as BadgeType} from '#/plugin/open-badge/tools/badges/prop-types'

import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from '#/plugin/open-badge/tools/badges/store/selectors'

import {reducer as parametersReducer} from '#/plugin/open-badge/tools/badges/parameters/store/reducer'

const reducer = combineReducers({
  badges: combineReducers({
    list: makeListReducer(selectors.STORE_NAME + '.badges.list', {}),
    mine: makeListReducer(selectors.STORE_NAME + '.badges.mine', {}),
    current: makeFormReducer(selectors.STORE_NAME + '.badges.current', {
      data: BadgeType.defaultProps
    }, {
      assertions: makeListReducer(selectors.STORE_NAME + '.badges.current.assertions')
    }),
    assertion: makeFormReducer(selectors.STORE_NAME + '.badges.assertion', {}, {
      evidences: makeListReducer(selectors.STORE_NAME + '.badges.assertion.evidences')
    })
  }),
  parameters: parametersReducer
})

export {
  reducer
}
