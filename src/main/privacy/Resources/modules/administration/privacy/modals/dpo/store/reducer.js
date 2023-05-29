import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from '#/main/privacy/administration/privacy/modals/dpo/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  new: true
})

export {
  reducer
}
