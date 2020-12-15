import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/modals/maintenance/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME)
