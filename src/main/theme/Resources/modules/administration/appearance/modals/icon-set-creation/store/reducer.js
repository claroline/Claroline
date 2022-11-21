import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/theme/administration/appearance/modals/icon-set-creation/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
