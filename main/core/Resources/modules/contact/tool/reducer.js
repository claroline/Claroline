import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {OPTIONS_LOAD} from '#/main/core/contact/tool/actions'

const reducer = makePageReducer({}, {
  options: makeReducer({}, {
    [OPTIONS_LOAD]: (state, action) => action.options
  }),
  contacts: makeListReducer('contacts'),
  visibleUsers: makeListReducer('visibleUsers')
})

export {
  reducer
}