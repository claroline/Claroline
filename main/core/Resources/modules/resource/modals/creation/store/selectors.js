import {createSelector} from 'reselect'

import {select as formSelect} from '#/main/core/data/form/selectors'

const STORE_NAME = 'resourceCreation'
const FORM_NAME  = `${STORE_NAME}.form`

const FORM_NODE_PART     = 'resourceNode'
const FORM_RESOURCE_PART = 'resource'

const store = (state) => state[STORE_NAME]

const parent = createSelector(
  [store],
  (store) => store.parent
)

const form = (state) => formSelect.form(state, FORM_NAME)

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
  FORM_NAME,
  FORM_NODE_PART,
  FORM_RESOURCE_PART,
  parent,
  newNode,
  form,
  saveEnabled
}
