import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/community/modals/teams/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
