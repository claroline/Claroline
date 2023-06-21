import {createSelector} from 'reselect'

import {selectors as paramSelectors} from '#/main/core/administration/parameters/store/selectors'

const STORE_NAME = 'authentication'
const FORM_NAME = paramSelectors.STORE_NAME+'.'+STORE_NAME

const store = createSelector(
  [paramSelectors.store],
  (baseStore) => baseStore[STORE_NAME]
)

export const selectors = {
  STORE_NAME,
  FORM_NAME,
  store
}


