import { makeReducer } from '#/main/app/store/reducer'
import { makeInstanceAction } from '#/main/app/store/actions'
import { selectors } from '#/main/privacy/administration/privacy/store/selectors'
import { TOOL_LOAD } from '#/main/core/tool/store'

const reducer = makeReducer(null, {
  [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.parameters
})

export {
  reducer
}