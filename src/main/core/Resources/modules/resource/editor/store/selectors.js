import {createSelector} from 'reselect'

import {selectors as formSelectors} from '#/main/app/content/form'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

const STORE_NAME = 'resourceEditor'

const store = (state) => state[STORE_NAME]

/**
 * Get the path of the current resource editor.
 * Used to create additional routing in the editor.
 */
const path = createSelector(
  [resourceSelectors.path],
  (resourcePath) => resourcePath + '/edit'
)

const data = (state) => formSelectors.data(formSelectors.form(state, STORE_NAME))

const errors = (state) => formSelectors.errors(formSelectors.form(state, STORE_NAME))

/**
 * Get currently edited resource node data.
 * NB. You get the modified version of the data.
 */
const resourceNode = (state) => formSelectors.value(formSelectors.form(state, STORE_NAME), 'resourceNode')

/**
 * Get currently edited resource data.
 * NB. You get the modified version of the data.
 */
const resource = (state) => formSelectors.value(formSelectors.form(state, STORE_NAME), 'resource')

/**
 * Get currently edited resource rights.
 * NB. You get the modified version of the rights.
 */
const rights = (state) => formSelectors.value(formSelectors.form(state, STORE_NAME), 'rights')

export const selectors = {
  STORE_NAME,

  path,
  data,
  errors,
  resourceNode,
  resource,
  rights
}
