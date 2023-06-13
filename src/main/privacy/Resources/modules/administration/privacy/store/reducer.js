import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

const reducer = makeReducer({}, {
  [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.parameters
})

export {
  reducer
}
