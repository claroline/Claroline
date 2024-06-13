
import {selectors} from '#/main/app/contexts/workspace/modals/creation/store/selectors'
import {makeFormReducer} from '#/main/app/content/form/store'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
