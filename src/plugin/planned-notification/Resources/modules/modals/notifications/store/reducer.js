import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/plugin/planned-notification/modals/notifications/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
