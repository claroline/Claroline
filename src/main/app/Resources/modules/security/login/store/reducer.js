import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/security/login/store/selectors'

export const reducer = makeFormReducer(selectors.FORM_NAME, {new: true})
