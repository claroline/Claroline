import {createSelector} from 'reselect'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

// retrieves a form instance in the store
const form = (state, formName) => get(state, formName)

const isNew = (formState) => formState.new
const mode = (formState) => formState.mode
const validating = (formState) => formState.validating
const pendingChanges = (formState) => formState.pendingChanges
const errors = (formState) => formState.errors
const data = (formState) => formState.data
const originalData = (formState) => formState.originalData

const value = (formState, prop) => get(data(formState), prop)

const valid = createSelector(
  [errors],
  (errors) => isEmpty(errors)
)

const saveEnabled = createSelector(
  [pendingChanges, validating, valid],
  (pendingChanges, validating, valid) => pendingChanges && (!validating || valid)
)

export const selectors = {
  form,
  isNew,
  mode,
  validating,
  pendingChanges,
  errors,
  data,
  originalData,
  valid,
  saveEnabled,
  value
}
