import get from 'lodash/get'

import {selectors as urlSelectors} from '#/plugin/url/resources/url/store/selectors'

const STORE_NAME = `${urlSelectors.STORE_NAME}.editor`

const FORM_NAME = `${STORE_NAME}.urlForm`

const availablePlaceholders = (state) => get(state, STORE_NAME+'.availablePlaceholders', [])

export const selectors = {
  FORM_NAME,
  availablePlaceholders
}
