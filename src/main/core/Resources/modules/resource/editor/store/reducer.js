import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {selectors} from '#/main/core/resource/editor/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: {
    resourceNode: ResourceNodeTypes.defaultProps,
    rights: []
  }
})

export {
  reducer
}
