import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/community/modals/groups/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'name', direction: 1}
})

export {
  reducer
}
