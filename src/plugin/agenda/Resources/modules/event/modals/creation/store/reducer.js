import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {selectors} from '#/plugin/agenda/event/modals/creation/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: EventTypes.defaultProps
})

export {
  reducer
}
