import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/claco-form/modals/category/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
