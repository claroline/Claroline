import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_RIGHTS_LOAD} from '#/main/core/tool/modals/rights/store/actions'
import {selectors} from '#/main/core/tool/modals/rights/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME, {data: []}, {
  data: makeReducer([], {
    [TOOL_RIGHTS_LOAD]: (state, action) => action.rights
  }),
  originalData: makeReducer([], {
    [TOOL_RIGHTS_LOAD]: (state, action) => action.rights
  })
})
