import {makeFormReducer} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/booking/tools/booking/material/modals/booking/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME)
