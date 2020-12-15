import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/competency/modals/abilities/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
