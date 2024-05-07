import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form'
import {selectors as baseSelectors} from '#/main/core/tool/store/selectors'

/**
 * @deprecated
 */
const STORE_NAME = baseSelectors.EDITOR_NAME

const form = (state) => formSelectors.form(state, baseSelectors.EDITOR_NAME)

/*const formData = createSelector(
  [form],
  (form) => formSelectors.
)*/

export const selectors = {
  /**
   * @deprecated
   */
  STORE_NAME
}
