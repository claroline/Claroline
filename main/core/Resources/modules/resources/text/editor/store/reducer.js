import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/main/core/resources/text/editor/store/selectors'

const reducer = {
  textForm: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.text || state
    }),
    originalData: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.text || state
    })
  })
}

export {
  reducer
}