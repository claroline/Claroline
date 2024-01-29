import {selectors as baseSelectors} from '#/main/community/tools/community/store/selectors'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const STORE_NAME = baseSelectors.STORE_NAME + '.groups'

const LIST_NAME = STORE_NAME+ '.list'
const FORM_NAME = STORE_NAME + '.current'

const currentId = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME)).id || null

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  FORM_NAME,

  currentId
}
