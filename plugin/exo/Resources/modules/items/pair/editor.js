import cloneDeep from 'lodash/cloneDeep'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {makeId, makeActionCreator} from './../../utils/utils'
import {notBlank, number, chain} from './../../utils/validate'
import {tex} from './../../utils/translate'
import {utils} from './utils/utils'
import {PairForm as component} from './editor.jsx'

const UPDATE_PROP = 'UPDATE_PROP'
const ADD_ITEM = 'ADD_ITEM'
const REMOVE_ITEM = 'REMOVE_ITEM'
const UPDATE_ITEM = 'UPDATE_ITEM'
const ADD_ITEM_COORDINATES = 'ADD_ITEM_COORDINATES'
const ADD_PAIR = 'ADD_PAIR'
const REMOVE_PAIR = 'REMOVE_PAIR'
const UPDATE_PAIR = 'UPDATE_PAIR'
const DROP_PAIR_ITEM = 'DROP_PAIR_ITEM'

export const actions = {
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  addItem:  makeActionCreator(ADD_ITEM, 'isOdd'),
  removeItem:  makeActionCreator(REMOVE_ITEM, 'id', 'isOdd'),
  updateItem:  makeActionCreator(UPDATE_ITEM, 'id', 'property', 'value', 'isOdd'),
  addPair: makeActionCreator(ADD_PAIR),
  removePair: makeActionCreator(REMOVE_PAIR, 'leftId', 'rightId'),
  updatePair: makeActionCreator(UPDATE_PAIR, 'index', 'property', 'value'),
  dropPairItem: makeActionCreator(DROP_PAIR_ITEM, 'pairData', 'itemData'),
  addItemCoordinates: makeActionCreator(ADD_ITEM_COORDINATES, 'itemId', 'brotherId', 'coords')
}

function decorate(pair) {

  // at least 2 "real" items (ie not odds)
  const itemDeletable = utils.getRealItemlist(pair.items, pair.solutions).length > 2
  const itemsWithDeletable = pair.items.map(
    item => Object.assign({}, item, {
      _deletable: itemDeletable
    })
  )

  let decorated = Object.assign({}, pair, {
    items: itemsWithDeletable
  })

  return decorated
}

function reduce(pair = {}, action) {
  switch (action.type) {

    case ITEM_CREATE: {
      return decorate(Object.assign({}, pair, {
        random: false,
        penalty: 0,
        items: [
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
        solutions: [
          {
            itemIds: [-1, -1],
            score: 1,
            feedback: '',
            ordered: false,
            _deletable: false
          }
        ],
        rows: 1
      }))
    }

    case UPDATE_PROP: {
      const newItem = cloneDeep(pair)
      const value = action.property === 'penalty' ? parseFloat(action.value) : Boolean(action.value)
      newItem[action.property] = value
      return newItem
    }

    case ADD_ITEM: {
      const newItem = cloneDeep(pair)
      const id = makeId()
      newItem.items.push({
        id: id,
        type: 'text/html',
        data: ''
      })

      if(action.isOdd) {
        const oddSolutionToAdd = {
          itemIds: [id],
          score: 0,
          feedback: ''
        }
        newItem.solutions.push(oddSolutionToAdd)
      }

      const itemDeletable = utils.getRealItemlist(newItem.items, newItem.solutions).length > 2
      newItem.items.forEach(el => el._deletable = itemDeletable)

      return newItem
    }

    case UPDATE_ITEM: {
      const newItem = cloneDeep(pair)

      const value = action.property === 'score' ? parseFloat(action.value) : action.value
      const itemToUpdate = newItem.items.find(el => el.id === action.id)

      if(!action.isOdd){
        itemToUpdate[action.property] = value
        // update pair item data if needed
        if (action.property === 'data') {
          newItem.solutions.map((solution) => {
            const solutionItemIdIndex = solution.itemIds.findIndex(id => id === action.id)
            if(-1 < solutionItemIdIndex){
              solution._data = action.value
            }
          })
        }
      } else {
        if (action.property === 'data') {
          itemToUpdate[action.property] = value
        } else {
          const oddSolution = newItem.solutions.find(el => el.itemIds[0] === action.id)
          oddSolution[action.property] = value
        }
      }

      return newItem
    }

    case REMOVE_ITEM: {

      const newItem = cloneDeep(pair)
      const itemIndex = newItem.items.findIndex(el => el.id === action.id)
      newItem.items.splice(itemIndex, 1)
      if(action.isOdd){
        const idx = newItem.solutions.findIndex(el => el.itemIds.length === 1 && el.itemIds[0] === action.id)
        newItem.solutions.splice(idx, 1)
      } else {
        // handle deletable state
        const itemDeletable = utils.getRealItemlist(newItem.items, newItem.solutions).length > 2
        newItem.items.forEach(el => el._deletable = itemDeletable)
        // remove item from solution associations
        const solutions = cloneDeep(newItem.solutions)
        solutions.forEach((solution) => {
          const solutionItemIdIndex = solution.itemIds.findIndex(id => id === action.id)
          if(-1 < solutionItemIdIndex){
            const solutionItem = newItem.solutions.find(el => el.itemIds[solutionItemIdIndex] === action.id)
            solutionItem.itemIds.splice(solutionItemIdIndex, 1)
          }
        })
      }

      return newItem
    }

    case ADD_ITEM_COORDINATES: {
      const newItem = cloneDeep(pair)

      const itemToUpdate = newItem.items.find(el => el.id === action.itemId)
      if(itemToUpdate['coordinates']) {
        delete itemToUpdate.coordinates
      } else {
        itemToUpdate['coordinates'] = action.coords
        // remove coordinates from brother object
        if (action.brotherId !== -1) {
          const brotherItem = newItem.items.find(el => el.id === action.brotherId)
          delete brotherItem.coordinates
        }
      }

      return newItem
    }

    case ADD_PAIR: {
      const newItem = cloneDeep(pair)
      newItem.solutions.push({
        itemIds: [-1, -1],
        score: 1,
        feedback: '',
        ordered: false
      })

      newItem.rows = newItem.solutions.filter(solution => solution.score > 0).length

      const realSolutions = utils.getRealSolutionList(newItem.solutions)
      realSolutions.forEach(solution => {
        solution._deletable = realSolutions.length > 1
      })
      return newItem
    }

    case REMOVE_PAIR: {
      const newItem = cloneDeep(pair)
      const idxToRemove = newItem.solutions.findIndex(solution => solution.itemIds[0] === action.leftId && solution.itemIds[1] === action.rightId)
      newItem.solutions.splice(idxToRemove, 1)
      newItem.rows = newItem.solutions.filter(solution => solution.score > 0).length
      const realSolutions = utils.getRealSolutionList(newItem.solutions)
      realSolutions.forEach(solution => {
        solution._deletable = realSolutions.length > 1
      })
      return newItem
    }

    case UPDATE_PAIR: {
      const newItem = cloneDeep(pair)
      // 'index', 'property', 'value'
      // can update score feedback and ordered
      const value = action.property === 'score' ? parseFloat(action.value) : action.property === 'ordered' ? Boolean(action.value) : action.value
      const solutionToUpdate = utils.getRealSolutionList(newItem.solutions)[action.index]
      solutionToUpdate[action.property] = value
      return newItem
    }

    case DROP_PAIR_ITEM: {
      const newItem = cloneDeep(pair)
      // pairData = pair data + position of item dropped (0 / 1) + index (index of real solution)
      // itemData = dropped item data
      const realSolutionList = utils.getRealSolutionList(newItem.solutions)
      const existingSolution = realSolutionList[action.pairData.index]
      existingSolution.itemIds[action.pairData.position] = action.itemData.id

      return newItem
    }
  }
  return pair
}

function validate(pair) {
  const errors = {}

  // penalty should be greater or equal to 0
  if (chain(pair.penalty, [notBlank, number])) {
    errors.item = tex('penalty_not_valid')
  }

  // random can not be used if no pinned item
  if (pair.random && pair.items.filter(item => item.hasOwnProperty('coordinates') && item.coordinates.length === 2).length === 0) {
    errors.item = tex('pair_random_needs_pin_item')
  }

  // no blank items / odds
  if (pair.items.find(item => notBlank(item.data, true))) {
    // item / odd data should not be empty
    errors.items = tex('item_empty_data_error')
  }

  // solutions and odd
  if (pair.solutions.length > 0) {
    // odd score not empty and valid number
    if (undefined !== pair.solutions.find(solution => solution.itemIds.length === 1 && chain(solution.score, [notBlank, number]) && solution.score > 0)) {
      errors.odd = tex('odd_score_not_valid')
    }

    // no pair with no score
    if (undefined !== pair.solutions.find(solution => solution.itemIds.length === 2 && chain(solution.score, [notBlank, number]))) {
      errors.solutions = tex('solution_score_not_valid')
    }

    // no pair with only one item...
    if (undefined !== pair.solutions.find(solution => solution.itemIds.length === 2 && solution.itemIds.indexOf(-1) !== -1)) {
      errors.solutions = tex('pair_solution_at_least_two_items')
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
