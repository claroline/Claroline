import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/plugin/home/tools/home/editor/modals/position/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
