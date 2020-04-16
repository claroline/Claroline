import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/core/modals/users/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'username', direction: 1}
})

export {
  reducer
}
