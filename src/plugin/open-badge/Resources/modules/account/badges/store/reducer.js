import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/open-badge/account/badges/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'issuedOn', direction: -1}
})

export {
  reducer
}
