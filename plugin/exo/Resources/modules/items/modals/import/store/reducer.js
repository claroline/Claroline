import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/exo/items/modals/import/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
