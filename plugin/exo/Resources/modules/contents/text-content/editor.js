import cloneDeep from 'lodash/cloneDeep'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {notBlank, setIfError} from '#/main/core/validation'

import {TextContent as component} from './editor.jsx'
import {TextObjectEditor as objectEditor} from './object-editor.jsx'

const UPDATE_ITEM_CONTENT_TEXT = 'UPDATE_ITEM_CONTENT_TEXT'

export const actions = {
  updateItemContentText: makeActionCreator(UPDATE_ITEM_CONTENT_TEXT, 'data')
}

function reduce(item = {}, action = {}) {
  let newItem

  switch (action.type) {
    case UPDATE_ITEM_CONTENT_TEXT:
      newItem = cloneDeep(item)
      newItem['data'] = action.data
      return newItem
  }
  return item
}

function validate(item) {
  const errors = {}
  setIfError(errors, 'data', notBlank(item.data, true))

  return errors
}

export default {
  component,
  reduce,
  validate,
  objectEditor
}
