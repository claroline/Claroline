import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/main/core/tool/editor/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: null,
  rights: null
})

export {
  reducer
}
