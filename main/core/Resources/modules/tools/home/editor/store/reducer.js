import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/core/tools/home/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {data: [], originalData: []}, {
  data: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'home')]: (state, action) => action.toolData.tabs
  }),
  originalData: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'home')]: (state, action) => action.toolData.tabs
  })
})

export {
  reducer
}
