import {createSelector} from 'reselect'
import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'privacy'
const FORM_NAME  = `${STORE_NAME}.parameters`

const parameters = (state) => state[STORE_NAME].data.parameters

const form = createSelector(
  [formSelectors.form],
  (form) => form[FORM_NAME]
)

const lockedParameters = (state) => form(state).lockedParameters

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  parameters,
  lockedParameters,
  form
}
