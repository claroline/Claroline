import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/security/password/reset/store/selectors'

export const reducer = makeFormReducer(selectors.FORM_NAME)
