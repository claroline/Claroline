import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/modals/sessions/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'name', direction: 1}
})

export {
  reducer
}
