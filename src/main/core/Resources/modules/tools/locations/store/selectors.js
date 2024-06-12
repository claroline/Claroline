import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'locations'

const currentLocation = (state) => formSelectors.originalData(formSelectors.form(state, STORE_NAME+'.current'))

export const selectors = {
  STORE_NAME,
  currentLocation
}
