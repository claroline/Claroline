import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/tool/modals/rights/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME)
