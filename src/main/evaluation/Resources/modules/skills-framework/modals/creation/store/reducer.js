
import {makeFormReducer} from '#/main/app/content/form/store'
import {selectors} from '#/main/evaluation/skills-framework/modals/creation/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  new: true
})

export {
  reducer
}
