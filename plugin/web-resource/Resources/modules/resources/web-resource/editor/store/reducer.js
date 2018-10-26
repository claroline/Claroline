import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/plugin/web-resource/resources/web-resource/editor/store/selectors'

const reducer = makeFormReducer(selectors.FORM_NAME, {}, {
  data: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.file
  }),
  initialData: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.file
  })
})


export {
  reducer
}
