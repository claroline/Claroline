
import {makeFormReducer} from '#/main/app/content/form/store'
import {selectors} from '#/plugin/open-badge/badge/modals/creation/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  new: true
})

export {
  reducer
}
