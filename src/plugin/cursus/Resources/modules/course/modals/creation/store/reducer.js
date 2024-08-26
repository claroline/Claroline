import {makeFormReducer} from '#/main/app/content/form/store'
import {selectors} from '#/plugin/cursus/course/modals/creation/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  new: true
})

export {
  reducer
}
