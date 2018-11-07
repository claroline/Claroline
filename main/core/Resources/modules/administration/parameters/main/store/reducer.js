import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/administration/parameters/main/store/selectors'

const reducer = {
  [selectors.FORM_NAME]: makeFormReducer(selectors.FORM_NAME)
}

export {
  reducer
}
