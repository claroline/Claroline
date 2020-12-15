import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/main/core/resources/directory/editor/store/selectors'

const reducer = {
  directoryForm: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => action.resourceData.directory || state
    }),
    originalData: makeReducer({}, {
      [makeInstanceAction(RESOURCE_LOAD, 'directory')]: (state, action) => action.resourceData.directory || state
    })
  })
}

export {
  reducer
}