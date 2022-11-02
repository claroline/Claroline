import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors as baseSelectors} from '#/main/community/administration/community/store/selectors'

const reducer = makeFormReducer(baseSelectors.STORE_NAME+'.parameters', {}, {
  originalData: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => action.toolData.parameters
  }),
  data: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'community')]: (state, action) => action.toolData.parameters
  })
})

export {
  reducer
}
