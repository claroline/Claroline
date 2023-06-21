import {createSelector} from 'reselect'
import {selectors as paramSelectors} from '#/main/core/administration/parameters/store/selectors'
import {selectors as formSelectors} from "#/main/app/content/form/store";

const STORE_NAME = 'authenticationParameters'

const store = createSelector(
  [paramSelectors.store],
  (baseStore) => baseStore[STORE_NAME]
)

export const selectors = {
  STORE_NAME,
  store
}


