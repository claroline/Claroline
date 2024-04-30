import {createSelector} from 'reselect'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

// retrieves a form instance in the store
const form = createSelector(
  [
    (state) => state,
    (state, formName) => formName
  ],
  (state, formName) => get(state, formName)
)

/**
 * Does the form create new data on save ?
 *
 * @return boolean
 */
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

/*const hasChanged = createSelector(
  [data, originalData, pendingChanges],
  (pendingChanges) => {
    if (!pendingChanges) {
      return false
    }

    return JSON.stringify(data) !== JSON.stringify(originalData)
  }
)*/

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
