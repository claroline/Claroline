import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import zipObject from 'lodash/zipObject'
import set from 'lodash/set'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {tex} from '#/main/core/translation'
import {notBlank} from '#/main/core/validation'

import {ITEM_CREATE} from './../../quiz/editor/actions'
import {SCORE_FIXED} from './../../quiz/enums'
import {makeId} from './../../utils/utils'
import {Ordering as component} from './editor.jsx'

const UPDATE_PROP = 'UPDATE_PROP'
const UPDATE_ITEM = 'UPDATE_ITEM'
const ADD_ITEM = 'ADD_ITEM'
const REMOVE_ITEM = 'REMOVE_ITEM'
const MOVE_ITEM = 'MOVE_ITEM'

export const MODE_INSIDE = 'inside'
export const MODE_BESIDE = 'beside'

export const DIRECTION_HORIZONTAL = 'horizontal'
export const DIRECTION_VERTICAL = 'vertical'

export const actions = {
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  updateItem: makeActionCreator(UPDATE_ITEM, 'id', 'property', 'value'),
  addItem: makeActionCreator(ADD_ITEM, 'isOdd'),
  removeItem: makeActionCreator(REMOVE_ITEM, 'id'),
  moveItem: makeActionCreator(MOVE_ITEM, 'id', 'swapId')
}

function decorate(ordering) {

  const solutionsById = zipObject(
    ordering.solutions.map(solution => solution.itemId),
    ordering.solutions
  )

  const itemsWithSolutions = ordering.items.map(
    item => Object.assign({}, item, {
      _score: solutionsById[item.id].score,
      _position: solutionsById[item.id].position || undefined,
      _feedback: solutionsById[item.id].feedback || '',
      _deletable: ordering.solutions.filter(solution => undefined !== solution.position).length > 2
    })
  )

  let decorated = Object.assign({}, ordering, {
    items: itemsWithSolutions
  })

  return decorated
}

function reduce(ordering = {}, action) {
  switch (action.type) {
    case ITEM_CREATE: {
      const firstChoiceId = makeId()
      const secondChoiceId = makeId()
      return decorate(Object.assign({}, ordering, {
        mode: MODE_INSIDE,
        direction: DIRECTION_VERTICAL,
        penalty: 0,
        items: [
          {
            id: firstChoiceId,
            type: 'text/html',
            data: ''
          },
          {
            id: secondChoiceId,
            type: 'text/html',
            data: ''
          }
        ],
        solutions: [
          {
            itemId: firstChoiceId,
            score: 1,
            position: 1,
            feedback: ''
          },
          {
            itemId: secondChoiceId,
            score: 1,
            position: 2,
            feedback: ''
          }
        ]
      }))
    }
    case UPDATE_PROP: {
      let value = action.value

      if (action.property === 'score.success' || action.property === 'score.failure') {
        value = parseFloat(value)
      }

      const newItem = cloneDeep(ordering)
      const property = set({}, action.property, value)
      merge(newItem, property)
      // if we change from beside mode to inside mode we have to remove every odd if any
      if (action.property === 'mode' && value === MODE_INSIDE) {
        newItem.solutions = ordering.solutions.filter(solution => undefined !== solution.position)
        newItem.items = ordering.items.filter(item => undefined !== newItem.solutions.find(solution => solution.itemId === item.id))
      }
      // if we change from score type SUM to score type FIXED update every score (odd / items)
      if (action.property === 'score.type' && value === SCORE_FIXED) {
        newItem.solutions.forEach(solution => {
          const item = newItem.items.find(item => item.id === solution.itemId)
          if (solution.position) {
            solution.score = 1
            item._score = 1
          } else {
            solution.score = 0
            item._score = 0
          }
        })
      }
      return newItem
    }
    case ADD_ITEM: {
      const newItem = cloneDeep(ordering)
      const isOdd = action.isOdd

      const newSolution = {
        itemId: makeId(),
        score: isOdd ? 0 : 1,
        feedback: '',
        position: isOdd ? undefined : newItem.solutions.filter(solution => undefined !== solution.position).length + 1
      }

      newItem.solutions.push(newSolution)
      newItem.items.push({
        id: newSolution.itemId,
        type: 'text/html',
        data: '',
        _deletable: true,
        _score: newSolution.score,
        _feedback: newSolution.feedback,
        _position: newSolution.position
      })

      newItem.items.forEach(
        item => item._deletable = newItem.solutions.filter(solution => undefined !== solution.position).length > 2
      )

      return newItem
    }
    case REMOVE_ITEM: {
      const newItem = cloneDeep(ordering)
      newItem.items = newItem.items.filter(item => item.id !== action.id)
      newItem.solutions = newItem.solutions.filter(solution => solution.itemId !== action.id)
      newItem.items.forEach(
        item => item._deletable = newItem.solutions.filter(solution => undefined !== solution.position).length > 2
      )
      return newItem
    }
    case MOVE_ITEM: {
      const newItem = cloneDeep(ordering)
      // previous index of the dragged item
      const itemIndex = ordering.items.findIndex(item => item.id === action.id)
      const solution = newItem.solutions.find(solution => solution.itemId === action.id)
      // new index of the dragged item
      const swapIndex = ordering.items.findIndex(item => item.id === action.swapId)
      const swapSolution = newItem.solutions.find(solution => solution.itemId === action.swapId)

      const tempItem = cloneDeep(ordering.items.find(item => item.id === action.id))
      const tempSwap = cloneDeep(ordering.items.find(item => item.id === action.swapId))

      newItem.items[swapIndex] = tempItem
      newItem.items[itemIndex] = tempSwap

      // update solutions
      solution.position = swapIndex + 1
      swapSolution.position = itemIndex + 1

      return newItem
    }
    case UPDATE_ITEM: {
      const value = action.property === 'score' ? Number(action.value) : action.value
      const newItem = cloneDeep(ordering)
      const itemIndex = newItem.items.findIndex(item => item.id === action.id)
      const decoratedName = action.property === 'data' ? 'data' : `_${action.property}`

      newItem.items[itemIndex][decoratedName] = value

      if (action.property === 'score' || action.property === 'feedback') {
        const solutionIndex = newItem.solutions.findIndex(
          solution => solution.itemId === action.id
        )
        newItem.solutions[solutionIndex][action.property] = value
      }

      return newItem
    }
  }
  return ordering
}

function validate(ordering) {
  const errors = {}

  if (ordering.items.find(item => notBlank(item.data, true))) {
    errors.items = tex('ordering_item_empty_data_error')
  }

  if (ordering.score.type === SCORE_FIXED) {
    if (ordering.score.failure >= ordering.score.success) {
      set(errors, 'score.failure', tex('fixed_failure_above_success_error'))
      set(errors, 'score.success', tex('fixed_success_under_failure_error'))
    }
  } else {
    if (!ordering.items.find(item => item._score > 0)) {
      errors.items = tex('ordering_no_correct_answer_error')
    }
  }

  return errors
}


export default {
  component,
  reduce,
  decorate,
  validate
}
