import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form'
import {selectors as baseSelectors} from '#/main/core/tool/store/selectors'

const STORE_NAME = baseSelectors.EDITOR_NAME

const form = (state) => formSelectors.form(state, STORE_NAME)

const data = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME))

const contextType = baseSelectors.contextType
const contextData = baseSelectors.contextData

/**
 * Get the path of the current tool editor.
 * Used to create additional routing in the editor.
 */
const path = createSelector(
  [baseSelectors.path],
  (toolPath) => toolPath + '/edit'
)

export const selectors = {
  STORE_NAME,

  path,
  contextType,
  contextData,
  data
}
