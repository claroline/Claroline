import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/resource/editor/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: {
    resourceNode: {},
    resource: {},
    rights: []
  }
})

export {
  reducer
}
