import cloneDeep from 'lodash/cloneDeep'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {tex} from '#/main/core/translation'

import {makeId} from '../../../utils/utils'
import {utils} from '../utils/utils'

const SELECT_ADD_SELECTION = 'SELECT_ADD_SELECTION'
const SELECT_UPDATE_ANSWER = 'SELECT_UPDATE_ANSWER'
const SELECT_REMOVE_SELECTION = 'SELECT_REMOVE_SELECTION'

export const actions = {
  selectUpdateAnswer: makeActionCreator(SELECT_UPDATE_ANSWER, 'value', 'selectionId', 'parameter'),
  selectAddSelection: makeActionCreator(SELECT_ADD_SELECTION, 'begin', 'end'),
  selectRemoveSelection: makeActionCreator(SELECT_REMOVE_SELECTION, 'selectionId')
}

export function reduce(item = {}, action) {
  switch (action.type) {
    case SELECT_ADD_SELECTION: {
      const selections = item.selections ? cloneDeep(item.selections): []
      const solutions = item.solutions ? cloneDeep(item.solutions): []
      const sum = utils.getRealOffsetFromBegin(selections, action.begin)
      const id = makeId()

      selections.push({
        id,
        begin: action.begin - sum,
        end: action.end - sum,
        _displayedBegin: action.begin,
        _displayedEnd: action.end
      })

      solutions.push({
        selectionId: id,
        score: 1
      })

      const text = utils.getTextFromDecorated(item._text)

      let newItem = Object.assign({}, item, {
        selections,
        _selectionPopover: true,
        _selectionId: id,
        solutions,
        text,
        _text: utils.makeTextHtml(text, selections)
      })

      return utils.cleanItem(newItem)

    }
    case SELECT_REMOVE_SELECTION: {
      //this is only valid for the default 'visible' one
      const selections = cloneDeep(item.selections)
      const solutions = cloneDeep(item.solutions)
      selections.splice(selections.findIndex(selection => selection.id === action.selectionId), 1)
      solutions.splice(solutions.findIndex(solution => solution.selectionId === action.selectionId), 1)
      item = Object.assign(
        {},
        item,
        {
          selections,
          solutions,
          _text: utils.makeTextHtml(item.text, item.mode === 'find' ? solutions : selections)
        }
      )

      return utils.cleanItem(item)
    }
    case SELECT_UPDATE_ANSWER: {
      const solutions = cloneDeep(item.solutions)
      const solution = solutions.find(solution => solution.selectionId === action.selectionId)
      solution[action.parameter] = action.value

      return Object.assign({}, item, {solutions})
    }
  }
  return item
}

export function validate(item) {
  const _errors = {}
  let hasValidAnswers = false

  item.solutions.forEach(solution => {
    if (solution.score > 0) {
      hasValidAnswers = true
    }
  })

  if (!hasValidAnswers) {
    _errors.text = tex('selection_text_must_have_valid_answers_error')
  }

  return _errors
}
