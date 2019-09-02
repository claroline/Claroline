import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {selectors} from '#/plugin/open-badge/tools/badges/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME + '.parameters',{
  originalData: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'open-badge')]: (state, action) => action.toolData.parameters
  }),
  data: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'open-badge')]: (state, action) => action.toolData.parameters
  })
})

export {
  reducer
}
