import {Badge as BadgeType} from '#/plugin/open-badge/tools/badges/prop-types'

import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {reducer as parametersReducer} from '#/plugin/open-badge/tools/badges/parameters/store/reducer'

const reducer = {
  badges: combineReducers({
    list: makeListReducer('badges.list', {}),
    mine: makeListReducer('badges.mine', {}),
    current: makeFormReducer('badges.current', {
      data: BadgeType.defaultProps
    }, {
      assertions: makeListReducer('badges.current.assertions')
    }),
    assertion: makeFormReducer('badges.assertion', {}, {
      evidences: makeListReducer('badges.assertion.evidences')
    })
  }),
  parameters: parametersReducer
}

export {
  reducer
}
