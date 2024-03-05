import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/tool/editor/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
