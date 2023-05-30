import { makeReducer } from '#/main/app/store/reducer'
import { makeInstanceAction } from '#/main/app/store/actions'
import { selectors } from '#/main/privacy/account/privacy/store/selectors'
import { TOOL_LOAD } from '#/main/core/tool/store'

const reducer = makeReducer(null, {
  [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
})

export {
  reducer
}

