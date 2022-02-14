import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/claco-form/modals/keyword/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
