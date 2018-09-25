import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/administration/workspace/workspace/modals/registration/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
})

export {
  reducer
}
