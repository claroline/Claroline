import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/open-badge/tools/badges/modals/evidence/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
