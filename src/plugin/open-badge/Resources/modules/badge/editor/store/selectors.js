
import {selectors as formSelectors} from '#/main/app/content/form'

const STORE_NAME = 'badgeEditor'
const FORM_NAME = STORE_NAME+'.form'

const data = (state) => formSelectors.data(formSelectors.form(state, FORM_NAME))

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  data
}
