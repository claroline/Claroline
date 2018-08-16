import {createSelector} from 'reselect'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

const STORE_NAME = 'resourceCreation'

const FORM_NODE_PART     = 'resourceNode'
const FORM_RESOURCE_PART = 'resource'

const form = (state) => formSelect.form(state, STORE_NAME)

const formData = createSelector(
  [form],
  (form) => formSelect.data(form)
)

const newNode = createSelector(
  [formData],
  (formData) => formData.resourceNode
)

const saveEnabled = createSelector(
  [form],
  (form) => formSelect.saveEnabled(form)
)

export const selectors = {
  STORE_NAME,
  FORM_NODE_PART,
  FORM_RESOURCE_PART,
  newNode,
  form,
  saveEnabled
}
