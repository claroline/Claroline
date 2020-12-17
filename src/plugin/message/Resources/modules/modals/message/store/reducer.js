import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/message/modals/message/store/selectors'

export const reducer = makeFormReducer(selectors.STORE_NAME)
