import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store'

import {selectors} from '#/plugin/forum/resources/forum/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {}, {
  data: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.forum
  }),
  originalData: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.forum
  })
})

export {
  reducer
}
