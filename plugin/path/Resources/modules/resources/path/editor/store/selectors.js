import {createSelector} from 'reselect'

import {selectors as pathSelectors} from '#/plugin/path/resources/path/store/selectors'

const FORM_NAME = pathSelectors.STORE_NAME+'.pathForm'

// todo : reuse form selector
const form = createSelector(
  [pathSelectors.resource],
  (resource) => resource.pathForm
)

// todo : reuse form selector
const path = createSelector(
  [form],
  (form) => form.data
)

const steps = createSelector(
  [path],
  (path) => path.steps || []
)

const stepCopy = createSelector(
  [form],
  (form) => form.copy
)

export const selectors = {
  FORM_NAME,
  form,
  path,
  stepCopy,
  steps
}
