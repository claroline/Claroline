import cloneDeep from 'lodash/cloneDeep'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {notBlank, number, chain} from '#/main/core/validation'
import {tex} from '#/main/core/translation'

import {SetForm as component} from './editor.jsx'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {makeId} from './../../utils/utils'

const UPDATE_PROP = 'UPDATE_PROP'

// ITEM OR ODD
const ADD_ITEM = 'ADD_ITEM'
const REMOVE_ITEM = 'REMOVE_ITEM'
const UPDATE_ITEM = 'UPDATE_ITEM'

// SETS
const ADD_SET = 'ADD_SET'
const UPDATE_SET = 'UPDATE_SET'
const REMOVE_SET = 'REMOVE_SET'

// SOLUTIONS.ASSOCIATION
const ADD_ASSOCIATION = 'ADD_ASSOCIATION'
const UPDATE_ASSOCIATION = 'UPDATE_ASSOCIATION'
const REMOVE_ASSOCIATION = 'REMOVE_ASSOCIATION'

export const actions = {
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  addItem:  makeActionCreator(ADD_ITEM, 'isOdd'),
  removeItem:  makeActionCreator(REMOVE_ITEM, 'id', 'isOdd'),
  updateItem:  makeActionCreator(UPDATE_ITEM, 'id', 'property', 'value', 'isOdd'),
  addSet:  makeActionCreator(ADD_SET),
  removeSet:  makeActionCreator(REMOVE_SET, 'id'),
  updateSet:  makeActionCreator(UPDATE_SET, 'id', 'property', 'value'),
  addAssociation: makeActionCreator(ADD_ASSOCIATION, 'setId', 'itemId', 'itemData'),
  removeAssociation: makeActionCreator(REMOVE_ASSOCIATION, 'setId', 'itemId'),
  updateAssociation: makeActionCreator(UPDATE_ASSOCIATION, 'setId', 'itemId', 'property', 'value')
}

function decorate(question) {

  const itemDeletable = question.items.filter(item => undefined === question.solutions.odd.find(el => el.itemId === item.id)).length > 1
  const itemsWithDeletable = question.items.map(
    item => Object.assign({}, item, {
      _deletable: itemDeletable
    })
  )

  const setsWithDeletable = question.sets.map(
    item => Object.assign({}, item, {
      _deletable: question.sets.length > 1
    })
  )

  // add item data to solution
  const associationsWithItemData = getAssociationsWithItemData(question)

  let decorated = Object.assign({}, question, {
    items: itemsWithDeletable,
    sets: setsWithDeletable,
    solutions: Object.assign({}, question.solutions, {
      associations: associationsWithItemData
    })
  })

  return decorated
}

function getAssociationsWithItemData(item){
  const withData = item.solutions.associations.map(
      association => {
        const questionItem = item.items.find(el => el.id === association.itemId)
        const data = questionItem !== undefined ? questionItem.data : ''
        association._itemData = data
        return association
      }
  )

  return withData
}

function reduce(item = {}, action) {

  switch (action.type) {

    case ITEM_CREATE: {
      return decorate(Object.assign({}, item, {
        random: false,
        penalty: 0,
        sets: [
          {
            id: makeId(),
            type: 'text/html',
            data: ''
          }
        ],
        items: [
          {
            id: makeId(),
            type: 'text/html',
            data: ''
          }
        ],
        solutions: {
          associations: [],
          odd: []
        }
      }))
    }

    case UPDATE_PROP: {
      const newItem = cloneDeep(item)
      const value = action.property === 'penalty' ? parseFloat(action.value) : Boolean(action.value)
      newItem[action.property] = value
      return newItem
    }

    case ADD_ITEM: {
      const newItem = cloneDeep(item)
      const toAdd = {
        id: makeId(),
        type: 'text/html',
        data: ''
      }

      newItem.items.push(toAdd)

      if(action.isOdd) {
        const oddSolutionToAdd = {
          itemId: toAdd.id,
          score: 0,
          feedback: ''
        }
        newItem.solutions.odd.push(oddSolutionToAdd)
      }

      // consider items that are not in solutions.odd
      const itemDeletable = newItem.items.filter(item => undefined === newItem.solutions.odd.find(el => el.itemId === item.id)).length > 1
      newItem.items.filter(item => undefined === newItem.solutions.odd.find(el => el.itemId === item.id)).forEach(el => el._deletable = itemDeletable)
      return newItem
    }

    case UPDATE_ITEM: {
      const newItem = cloneDeep(item)

      const value = action.property === 'score' ? parseFloat(action.value) : action.value
      const itemToUpdate = newItem.items.find(el => el.id === action.id)
      // if it's a normal item only data can be updated
      if(!action.isOdd){
        itemToUpdate[action.property] = value
        // update associations item data
        newItem.solutions.associations.map((ass) => {
          if(ass.itemId === action.id){
            ass._itemData = action.value
          }
        })
      } else {
        if (action.property === 'data') {
          itemToUpdate[action.property] = value
        } else {
          const oddSolution = newItem.solutions.odd.find(el => el.itemId = action.id)
          oddSolution[action.property] = value
        }
      }

      return newItem
    }

    case REMOVE_ITEM: {
      const newItem = cloneDeep(item)
      const itemIndex = newItem.items.findIndex(el => el.id === action.id)
      newItem.items.splice(itemIndex, 1)
      if(action.isOdd){
        // remove item from solution odds
        const odd = cloneDeep(newItem.solutions.odd)
        odd.forEach((odd) => {
          if(odd.itemId === action.id){
            const idx = newItem.solutions.odd.findIndex(el => el.itemId === action.id)
            newItem.solutions.odd.splice(idx, 1)
          }
        })
      } else {
        // consider items that are not in solutions.odd
        const itemDeletable = newItem.items.filter(item => undefined === newItem.solutions.odd.find(el => el.itemId === item.id)).length > 1
        newItem.items.filter(item => undefined === newItem.solutions.odd.find(el => el.itemId === item.id)).forEach(el => el._deletable = itemDeletable)
        // remove item from solution associations
        const associations = cloneDeep(newItem.solutions.associations)
        associations.forEach((ass) => {
          if(ass.itemId === action.id){
            const idx = newItem.solutions.associations.findIndex(el => el.itemId === action.id)
            newItem.solutions.associations.splice(idx, 1)
          }
        })
      }

      return newItem
    }

    case ADD_SET: {
      const newItem = cloneDeep(item)
      newItem.sets.push({
        id: makeId(),
        type: 'text/html',
        data: ''
      })
      newItem.sets.forEach(set => set._deletable = newItem.sets.length > 1)
      return newItem
    }

    case UPDATE_SET: {
      const newItem = cloneDeep(item)
      const toUpdate = newItem.sets.find(el => el.id === action.id)
      toUpdate[action.property] = action.value
      return newItem
    }

    case REMOVE_SET: {
      const newItem = cloneDeep(item)
      const index = newItem.sets.findIndex(set => set.id === action.id)
      newItem.sets.splice(index, 1)
      newItem.sets.forEach(set => set._deletable = newItem.sets.length > 1)
      // remove set from solution
      newItem.solutions.associations.forEach((ass, index) => {
        if(ass.setId === action.id){
          // remove
          newItem.solutions.associations.splice(index, 1)
        }
      })

      return newItem
    }

    case ADD_ASSOCIATION: {
      const newItem = cloneDeep(item)
      const toAdd = {
        itemId: action.itemId,
        setId: action.setId,
        score: 1,
        feedback: '',
        _itemData: action.itemData
      }
      newItem.solutions.associations.push(toAdd)
      return newItem
    }

    case REMOVE_ASSOCIATION: {
      const newItem = cloneDeep(item)
      const index = newItem.solutions.associations.findIndex(el => el.itemId === action.itemId && el.setId === action.setId)
      newItem.solutions.associations.splice(index, 1)
      return newItem
    }

    case UPDATE_ASSOCIATION: {
      const newItem = cloneDeep(item)
      const value = action.property === 'score' ? parseFloat(action.value) : action.value
      const association = newItem.solutions.associations.find(el => el.setId === action.setId && el.itemId === action.itemId)
      association[action.property] = value
      return newItem
    }

  }
  return item
}

function validate(item) {
  const errors = {}

  // penalty should be greater or equal to 0
  if (chain(item.penalty, [notBlank, number])) {
    errors.item = tex('set_penalty_not_valid')
  }

  // one item (that is not an odd) min
  if (item.items.filter(el => undefined === item.solutions.odd.find(odd => odd.itemId === el.id)).length === 0) {
    errors.items = tex('set_at_least_one_item')
  } else if (item.items.filter(el => undefined === item.solutions.odd.find(odd => odd.itemId === el.id)).find(item => notBlank(item.data, true))) {
    // item data should not be empty
    errors.items = tex('set_item_empty_data_error')
  }

  // no item (that are not odd items) should be orphean (ie not used in any set)
  if (item.items.some(el => {
    return item.solutions.associations.find(association => association.itemId === el.id) === undefined &&
      item.solutions.odd.find(o => o.itemId === el.id) === undefined
  })){
    errors.items = tex('set_no_orphean_items')
  }

  // one set min
  if (item.sets.length === 0) {
    errors.sets = tex('set_at_least_one_set')
  } else if (item.sets.find(set => notBlank(set.data, true))) {
    // set data should not be empty
    errors.sets = tex('set_set_empty_data_error')
  }

  if (item.solutions.associations.length === 0) {
    errors.solutions = tex('set_no_solution')
  } else if (undefined !== item.solutions.associations.find(association => chain(association.score, [notBlank, number]))) {
    // each solution should have a valid score
    errors.solutions = tex('set_score_not_valid')
  } else if (undefined === item.solutions.associations.find(association => association.score > 0)) {
    // at least one solution with a score that is greater than 0
    errors.solutions = tex('set_no_valid_solution')
  }

  // odd
  if (item.solutions.odd.length > 0) {
    // odd score not empty and valid number
    if(undefined !== item.solutions.odd.find(odd => chain(odd.score, [notBlank, number]))) {
      errors.odd = tex('set_score_not_valid')
    } else if (undefined !== item.solutions.odd.find(odd => odd.score > 0)) {
      errors.odd = tex('set_odd_score_not_valid')
    }
    // odd data not empty
    if (item.items.filter(el => undefined !== item.solutions.odd.find(odd => odd.itemId === el.id)).find(odd => notBlank(odd.data, true))) {
      // set data should not be empty
      errors.odd = tex('set_odd_empty_data_error')
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
