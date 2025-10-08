import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/lesson/resources/lesson/modals/position/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
