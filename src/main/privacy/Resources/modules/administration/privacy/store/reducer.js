import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {selectors} from './selectors'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

const reducer = {
  privacy: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)
  })
}

export {
  reducer
}
