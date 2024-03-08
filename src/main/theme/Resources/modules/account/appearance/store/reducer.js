import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store'
import {selectors} from '#/main/theme/account/appearance/store/selectors'

const reducer = combineReducers({
  availableThemes: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'appearance')]: (state, action) => action.toolData.availableThemes
  }),
  form: makeFormReducer(selectors.FORM_NAME, {
    new: false
  }, {
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'appearance')]: (state, action) => action.toolData.theme
    }),
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'appearance')]: (state, action) => action.toolData.theme
    })
  })
})

export {
  reducer
}
