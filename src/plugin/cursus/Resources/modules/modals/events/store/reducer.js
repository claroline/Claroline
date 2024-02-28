import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/cursus/modals/events/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME, {
  sortBy: {property: 'startDate', direction: -1}
})

export {
  reducer
}
