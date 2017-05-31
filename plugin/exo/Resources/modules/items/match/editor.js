import cloneDeep from 'lodash/cloneDeep'
import merge from 'lodash/merge'
import set from 'lodash/set'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {notBlank, number, chain} from '#/main/core/validation'
import {tex} from '#/main/core/translation'

import {Match as component} from './editor.jsx'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {makeId} from './../../utils/utils'

const UPDATE_SOLUTION = 'UPDATE_SOLUTION'
const ADD_SOLUTION = 'ADD_SOLUTION'
const REMOVE_SOLUTION = 'REMOVE_SOLUTION'
const UPDATE_PROP = 'UPDATE_PROP'
const ADD_ITEM = 'ADD_ITEM'
const REMOVE_ITEM = 'REMOVE_ITEM'
const UPDATE_ITEM = 'UPDATE_ITEM'

export const actions = {
  updateSolution: makeActionCreator(UPDATE_SOLUTION, 'firstId', 'secondId', 'property', 'value'),
  addSolution: makeActionCreator(ADD_SOLUTION, 'solution'),
  removeSolution: makeActionCreator(REMOVE_SOLUTION, 'firstId', 'secondId'),
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  addItem: makeActionCreator(ADD_ITEM, 'isLeftSet'),
  updateItem: makeActionCreator(UPDATE_ITEM, 'isLeftSet', 'id', 'value'),
  removeItem: makeActionCreator(REMOVE_ITEM, 'isLeftSet', 'id')
}

function decorate(item) {

  const leftItemDeletable = getLeftItemDeletable(item)
  const firstSetWithDeletable = item.firstSet.map(
    set => Object.assign({}, set, {
      _deletable: leftItemDeletable
    })
  )

  const rightItemDeletable = getRightItemDeletable(item)
  const secondSetWithDeletable = item.secondSet.map(
    set => Object.assign({}, set, {
      _deletable: rightItemDeletable
    })
  )

  const solutionsWithDeletable = item.solutions.map(
    solution => Object.assign({}, solution, {
      _deletable: item.solutions.length > 0
    })
  )

  let decorated = Object.assign({}, item, {
    firstSet: firstSetWithDeletable,
    secondSet: secondSetWithDeletable,
    solutions: solutionsWithDeletable
  })

  return decorated
}

function reduce(item = {}, action) {
  switch (action.type) {
    case ITEM_CREATE: {
      return decorate(Object.assign({}, item, {
        random: false,
        penalty: 0,
        firstSet: [
          {
            id: makeId(),
            type: 'text/html',
            data: ''
          }
        ],
        secondSet: [
          {
            id: makeId(),
            type: 'text/html',
            data: ''
          },
          {
            id: makeId(),
            type: 'text/html',
            data: ''
          }
        ],
        solutions: []
      }))
    }

    case ADD_SOLUTION: {
      const newItem = cloneDeep(item)
      newItem.solutions.push(action.solution)
      newItem.solutions.forEach(solution => solution._deletable = newItem.solutions.length > 1)
      return newItem
    }

    case UPDATE_SOLUTION: {
      const newItem = cloneDeep(item)
      const value = action.property === 'score' ? parseFloat(action.value) : action.value
      let solution = newItem.solutions.find(solution => solution.firstId === action.firstId && solution.secondId === action.secondId)
      // mark as touched
      newItem._touched = merge(
        newItem._touched || {},
        set({}, action.property, true)
      )

      solution[action.property] = value
      return newItem
    }

    case REMOVE_SOLUTION: {
      const newItem = cloneDeep(item)
      const solutionIndex = newItem.solutions.findIndex(solution => solution.firstId === action.firstId && solution.secondId === action.secondId)
      newItem.solutions.splice(solutionIndex, 1)
      newItem.solutions.forEach(solution => solution._deletable = newItem.solutions.length > 1)
      return newItem
    }

    case UPDATE_PROP: {
      const newItem = cloneDeep(item)
      const value = action.property === 'penalty' ? parseFloat(action.value) : Boolean(action.value)
      // mark as touched
      newItem._touched = merge(
        newItem._touched || {},
        set({}, action.property, true)
      )
      newItem[action.property] = value
      return newItem
    }

    case ADD_ITEM: {

      const toAdd = {
        id: makeId(),
        type: 'text/html',
        data: ''
      }

      const newItem = cloneDeep(item)
      action.isLeftSet === true ? newItem.firstSet.push(toAdd) : newItem.secondSet.push(toAdd)

      const leftItemDeletable = getLeftItemDeletable(newItem)
      newItem.firstSet.forEach(set => set._deletable = leftItemDeletable)
      const rightItemDeletable = getRightItemDeletable(newItem)
      newItem.secondSet.forEach(set => set._deletable = rightItemDeletable)
      return newItem
    }

    case UPDATE_ITEM: {
      // type could be updated... but how ? text/html is always true ?
      const newItem = cloneDeep(item)
      // mark as touched (for now only data can be updated)
      newItem._touched = merge(
        newItem._touched || {},
        set({}, 'data', true)
      )
      let updatedSet = null
      if (action.isLeftSet) {
        updatedSet = newItem.firstSet.find(set => set.id === action.id)
      } else {
        updatedSet = newItem.secondSet.find(set => set.id === action.id)
      }
      updatedSet.data = action.value

      return newItem
    }

    case REMOVE_ITEM: {
      const newItem = cloneDeep(item)
      if (action.isLeftSet) {
        const setIndex = newItem.firstSet.findIndex(set => set.id === action.id)
        newItem.firstSet.splice(setIndex, 1)
      } else {
        const setIndex = newItem.secondSet.findIndex(set => set.id === action.id)
        newItem.secondSet.splice(setIndex, 1)
      }
      const solutionsToRemove = newItem.solutions.filter(solution => action.isLeftSet ? solution.firstId === action.id : solution.secondId === action.id)
      for(const solution of solutionsToRemove){
        const index = newItem.solutions.indexOf(solution)
        newItem.solutions.splice(index, 1)
      }
      const rightItemDeletable = getRightItemDeletable(newItem)
      newItem.firstSet.forEach(set => set._deletable = rightItemDeletable)
      const leftItemDeletable = getLeftItemDeletable(newItem)
      newItem.secondSet.forEach(set => set._deletable = leftItemDeletable)
      return newItem
    }
  }
  return item
}

function getRightItemDeletable(item){
  return (item.secondSet.length > 1 && item.firstSet.length > 1) || (item.secondSet.length > 2 && item.firstSet.length === 1)
}

function getLeftItemDeletable(item){
  return (item.secondSet.length > 1 && item.firstSet.length > 1) || (item.secondSet.length === 1 && item.firstSet.length > 2)
}

function validate(item) {
  const errors = {}

  // penalty greater than 0 and negatives score on solutions
  if (item.penalty && item.penalty > 0 && item.solutions.length > 0 && item.solutions.filter(solution => solution.score < 0).length > 0) {
    errors.warning = tex('match_warning_penalty_and_negative_scores')
  }

  // at least one solution
  if (item.solutions.length === 0) {
    errors.solutions = tex('match_no_solution')
  } else if (undefined !== item.solutions.find(solution => chain(solution.score, [notBlank, number]))) {
    // each solution should have a valid score
    errors.solutions = tex('match_score_not_valid')
  } else if (undefined === item.solutions.find(solution => solution.score > 0)) {
    // at least one solution with a score that is greater than 0
    errors.solutions = tex('match_no_valid_solution')
  }

  // no blank item data
  if (item.firstSet.find(set => notBlank(set.data, true)) || item.secondSet.find(set => notBlank(set.data, true))) {
    errors.items = tex('match_item_empty_data_error')
  }

  // empty penalty
  if (chain(item.penalty, [notBlank, number])) {
    errors.items = tex('match_penalty_not_valid')
  }

  return errors
}


export default {
  component,
  decorate,
  reduce,
  validate
}
