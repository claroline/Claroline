import cloneDeep from 'lodash/cloneDeep'
import difference from 'lodash/difference'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {makeInstanceReducer, combineReducers, reduceReducers} from '#/main/app/store/reducer'
import {cleanErrors} from '#/main/app/content/form/utils'

import {constants} from '#/main/app/content/form/constants'
import {
  FORM_RESET,
  FORM_SET_MODE,
  FORM_SET_ERRORS,
  FORM_SUBMIT,
  FORM_UPDATE
} from '#/main/app/content/form/store/actions'

const defaultState = {
  new: false,
  mode: constants.FORM_MODE_DEFAULT,
  validating: false,
  pendingChanges: false,
  errors: {},
  data: {},
  originalData: {}
}

const baseReducer = {
  new: makeInstanceReducer(defaultState.new, {
    [FORM_RESET]: (state, action) => !!action.isNew
  }),

  mode: makeInstanceReducer(defaultState.mode, {
    [FORM_SET_MODE]: (state, action) => action.mode
  }),

  /**
   * Reduces the validating state of the form.
   * (becomes true on form submission)
   */
  validating: makeInstanceReducer(defaultState.validating, {
    [FORM_RESET]: () => defaultState.validating,
    [FORM_SUBMIT]: () => true,
    [FORM_UPDATE]: () => false
  }),

  pendingChanges: makeInstanceReducer(defaultState.pendingChanges, {
    [FORM_RESET]: () => defaultState.pendingChanges,
    [FORM_UPDATE]: () => true
  }),

  /**
   * Reduces the errors of the form.
   */
  errors: makeInstanceReducer(defaultState.errors, {
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
  }),

  /**
   * Reduces the data of the form.
   */
  data: makeInstanceReducer(defaultState.data, {
    [FORM_RESET]: (state, action) => action.data || {},
    [FORM_UPDATE]: (state, action) => {
      if (action.path) {
        // update correct property
        const newState = cloneDeep(state)
        set(newState, action.path, action.value)

        return newState
      }

      return action.value
    }
  }),

  originalData: makeInstanceReducer(defaultState.originalData, {
    [FORM_RESET]: (state, action) => action.data || {}
  })
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
