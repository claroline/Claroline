import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/log/account/security/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'date', direction: -1}
})

export {
  reducer
}
