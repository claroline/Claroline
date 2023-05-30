import { makeReducer } from '#/main/app/store/reducer'
import { makeInstanceAction } from '#/main/app/store/actions'
import { TOOL_LOAD } from '#/main/core/tool/store'
import {makeFormReducer} from '#/main/app/content/form/store'
import {selectors} from '#/main/privacy/administration/privacy/modals/country/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {}, {
  data: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters
  }),
  originalData: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters
  })
})

export {
  reducer
}
