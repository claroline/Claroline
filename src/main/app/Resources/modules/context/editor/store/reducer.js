import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store'

import {CONTEXT_LOAD_AVAILABLE_TOOLS} from '#/main/app/context/editor/store/actions'
import {selectors} from '#/main/app/context/editor/store/selectors'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME),
  availableTools: makeReducer([], {
    [CONTEXT_LOAD_AVAILABLE_TOOLS]: (state, action) => action.tools || []
  })
})

export {
  reducer
}
