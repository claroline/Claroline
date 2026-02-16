import difference from 'lodash/difference'
import merge from 'lodash/merge'

import {combineReducers, makeInstanceReducer, reduceReducers} from '#/main/app/store/reducer'

import {constants} from '#/main/app/api/fetch/constants'
import {
  API_FETCH_FAILED,
  API_FETCH_FULFILLED,
  API_FETCH_INVALIDATE,
  API_FETCH_PENDING,
  API_FETCH_RELOAD
} from '#/main/app/api/fetch/store/actions'

const defaultState = {
  status: constants.STATUS_IDLE,
  errorCode: null,
  error: null,
  data: null,
  invalidated: false
}

const baseReducer = {
  status: makeInstanceReducer(defaultState.status, {
    [API_FETCH_PENDING]: () => constants.STATUS_PENDING,
    [API_FETCH_FULFILLED]: () => constants.STATUS_SUCCEEDED,
    [API_FETCH_FAILED]: () => constants.STATUS_FAILED
  }),

  errorCode: makeInstanceReducer(defaultState.errorCode, {
    [API_FETCH_FULFILLED]: () => null,
    [API_FETCH_FAILED]: (state, action) => action.errorCode
  }),

  error: makeInstanceReducer(defaultState.error, {
    [API_FETCH_FULFILLED]: () => null,
    [API_FETCH_FAILED]: (state, action) => action.error
  }),

  invalidated: makeInstanceReducer(defaultState.invalidated, {
    [API_FETCH_INVALIDATE]: () => true,
    [API_FETCH_FULFILLED]: () => false,
    [API_FETCH_FAILED]: () => false
  }),

  data: makeInstanceReducer(defaultState.data, {
    [API_FETCH_FULFILLED]: (state, action) => action.response,
    [API_FETCH_FAILED]: (state, action) => action.data || state,
    [API_FETCH_RELOAD]: (state, action) => action.data
  })
}

function makeFetchReducer(dataName, initialState = {}, customReducer = {}) {
  const reducer = {}

  const formState = merge({}, defaultState, initialState)

  // enhance base form reducers with custom ones if any
  Object.keys(baseReducer).map(reducerName => {
    reducer[reducerName] = customReducer[reducerName] ?
      reduceReducers(baseReducer[reducerName](dataName, formState[reducerName]), customReducer[reducerName]) : baseReducer[reducerName](dataName, formState[reducerName])
  })

  // get custom keys
  const rest = difference(Object.keys(customReducer), Object.keys(baseReducer))
  rest.map(reducerName =>
    reducer[reducerName] = customReducer[reducerName]
  )

  return combineReducers(reducer)
}

export {
  makeFetchReducer
}
