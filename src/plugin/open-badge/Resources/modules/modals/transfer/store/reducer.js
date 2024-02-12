import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/open-badge/modals/transfer/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME)
