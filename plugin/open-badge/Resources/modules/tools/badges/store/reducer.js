import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {Badge as BadgeType} from '#/plugin/open-badge/tools/badges/prop-types'
import {selectors} from '#/plugin/open-badge/tools/badges/store/selectors'
import {reducer as parametersReducer} from '#/plugin/open-badge/tools/badges/parameters/store/reducer'

const reducer = combineReducers({
  badges: combineReducers({
    list: makeListReducer(selectors.STORE_NAME + '.badges.list', {
      sortBy: {property: 'name', direction: 1}
    }, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true,
        [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME + '.badges.current']: () => true
      })
    }),
    mine: makeListReducer(selectors.STORE_NAME + '.badges.mine', {}, {
      invalidated: makeReducer(false, {
        [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
      })
    }),
    current: makeFormReducer(selectors.STORE_NAME + '.badges.current', {
      data: BadgeType.defaultProps
    }, {
      assertions: makeListReducer(selectors.STORE_NAME + '.badges.current.assertions', {}, {
        invalidated: makeReducer(false, {
          [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
        })
      })
    }),
    assertion: makeFormReducer(selectors.STORE_NAME + '.badges.assertion', {}, {
      evidences: makeListReducer(selectors.STORE_NAME + '.badges.assertion.evidences', {}, {
        invalidated: makeReducer(false, {
          [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
        })
      })
    })
  }),
  parameters: parametersReducer
})

export {
  reducer
}
