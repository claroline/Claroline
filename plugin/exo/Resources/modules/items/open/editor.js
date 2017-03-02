import cloneDeep from 'lodash/cloneDeep'
import {Open as component} from './editor.jsx'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {setIfError, notBlank, number, gteZero, chain} from './../../utils/validate'
import {makeActionCreator} from './../../utils/utils'
import {SCORE_MANUAL} from './../../quiz/enums'

const UPDATE = 'UPDATE'

export const actions = {
  update: makeActionCreator(UPDATE, 'property', 'value')
}

function reduce(item = {}, action) {
  switch (action.type) {
    case ITEM_CREATE: {
      return Object.assign({}, item, {
        contentType: 'text',
        score: {
          type: SCORE_MANUAL,
          max: 0
        },
        maxLength: 0,
        solutions: []
      })
    }

    case UPDATE: {
      const newItem = cloneDeep(item)
      const value = parseFloat(action.value)

      if (action.property === 'maxScore') {
        newItem.score.max = value
      } else {
        newItem[action.property] = value
      }
      return newItem
    }
  }
  return item
}


function validate(item) {
  const errors = {}
  setIfError(errors, 'maxScore', chain(item.score.max, [notBlank, number, gteZero]))
  setIfError(errors, 'maxLength', chain(item.maxLength, [notBlank, number, gteZero]))

  return errors
}

export default {
  component,
  reduce,
  validate
}
