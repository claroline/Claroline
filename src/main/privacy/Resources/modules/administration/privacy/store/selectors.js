import {selectors as formSelectors} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'privacy'
const store = (state) => state[STORE_NAME]

export const selectors = {
  STORE_NAME,
  store
}