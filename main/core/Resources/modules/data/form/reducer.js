import cloneDeep from 'lodash/cloneDeep'
import difference from 'lodash/difference'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {makeInstanceReducer, combineReducers, reduceReducers} from '#/main/core/scaffolding/reducer'
import {cleanErrors} from '#/main/core/data/form/utils'

import {
  FORM_RESET,
  FORM_SET_ERRORS,
  FORM_SUBMIT,
  FORM_UPDATE_PROP
} from './actions'

const defaultState = {
  new: false,
  validating: false,
  pendingChanges: false,
  errors: {},
  data: {},
  originalData: {}
}

const newReducer = makeInstanceReducer(defaultState.new, {
  [FORM_RESET]: (state, action) => !!action.isNew
})

/**
 * Reduces the validating state of the form.
 * (becomes true on form submission)
 */
const validatingReducer = makeInstanceReducer(defaultState.validating, {
  [FORM_RESET]: () => defaultState.validating,
  [FORM_SUBMIT]: () => true,
  [FORM_UPDATE_PROP]: () => false
})

const pendingChangesReducer = makeInstanceReducer(defaultState.pendingChanges, {
  [FORM_RESET]: () => defaultState.pendingChanges,
  [FORM_UPDATE_PROP]: () => true
})

/**
 * Reduces the errors of the form.
 */
const errorsReducer = makeInstanceReducer(defaultState.errors, {
  /**
   * Resets to default (aka empty) when the form is reset.
   */
  [FORM_RESET]: () => defaultState.errors,

  /**
   * Sets form validation errors.
   * It MUST receive `undefined` value for fixed errors in order to remove them from store.
   *
   * @param state
   * @param action
   */
  [FORM_SET_ERRORS]: (state, action) => cleanErrors(state, action.errors)
})

/**
 * Reduces the data of the form.
 */
const dataReducer = makeInstanceReducer(defaultState.data, {
  [FORM_RESET]: (state, action) => action.data || {},
  [FORM_UPDATE_PROP]: (state, action) => {
    const newState = cloneDeep(state)

    // update correct property
    set(newState, action.propName, action.propValue)

    return newState
  }
})

const originalDataReducer = makeInstanceReducer(defaultState.originalData, {
  [FORM_RESET]: (state, action) => action.data || {}
})

const baseReducer = {
  new: newReducer,
  validating: validatingReducer,
  pendingChanges: pendingChangesReducer,
  errors: errorsReducer,
  data: dataReducer,
  originalData: originalDataReducer
}

/**
 * Creates reducer for forms.
 *
 * @param {string} formName      - the name of the form.
 * @param {object} initialState  - the initial state of the form instance.
 * @param {object} customReducer - an object containing custom handlers.
 *
 * @returns {function}
 */
function makeFormReducer(formName, initialState = {}, customReducer = {}) {
  const reducer = {}

  const formState = merge({}, defaultState, initialState)

  // enhance base form reducers with custom ones if any
  Object.keys(baseReducer).map(reducerName => {
    reducer[reducerName] = customReducer[reducerName] ?
      reduceReducers(baseReducer[reducerName](formName, formState[reducerName]), customReducer[reducerName]) : baseReducer[reducerName](formName, formState[reducerName])
  })

  // get custom keys
  const rest = difference(Object.keys(customReducer), Object.keys(baseReducer))
  rest.map(reducerName =>
    reducer[reducerName] = customReducer[reducerName]
  )

  return combineReducers(reducer)
}

export {
  makeFormReducer
}
