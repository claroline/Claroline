import cloneDeep from 'lodash/cloneDeep'

import {tex} from '#/main/core/translation'
import {makeActionCreator} from '#/main/core/utilities/redux'

import {makeId} from '../../../utils/utils'
import {utils} from '../utils/utils'

const FIND_ADD_ANSWER = 'FIND_ADD_ANSWER'
const FIND_UPDATE_ANSWER = 'FIND_UPDATE_ANSWER'
const FIND_REMOVE_ANSWER = 'FIND_REMOVE_ANSWER'

export const actions = {
  findUpdateAnswer: makeActionCreator(FIND_UPDATE_ANSWER, 'value', 'selectionId', 'parameter'),
  findAddAnswer: makeActionCreator(FIND_ADD_ANSWER, 'begin', 'end'),
  findRemoveAnswer: makeActionCreator(FIND_REMOVE_ANSWER, 'selectionId')
}

export function reduce(item = {}, action) {
  switch (action.type) {
    case FIND_ADD_ANSWER: {
      const solutions = item.solutions ? cloneDeep(item.solutions): []
      const sum = utils.getRealOffsetFromBegin(solutions, action.begin)
      const id = makeId()

      solutions.push({
        selectionId: id,
        score: 1,
        begin: action.begin - sum,
        end: action.end - sum,
        _displayedBegin: action.begin,
        _displayedEnd: action.end
      })

      const text = utils.getTextFromDecorated(item._text)

      let newItem = Object.assign({}, item, {
        _selectionPopover: true,
        _selectionId: id,
        solutions,
        tries: item.tries + 1,
        text,
        _text: utils.makeTextHtml(text, solutions)
      })

      return utils.cleanItem(newItem)
    }
    case FIND_REMOVE_ANSWER: {
      //this is only valid for the default 'visible' one
      const solutions = cloneDeep(item.solutions)
      solutions.splice(solutions.findIndex(solution => solution.selectionId === action.selectionId), 1)
      item = Object.assign(
        {},
        item,
        {
          solutions,
          _text: utils.makeTextHtml(item.text, solutions, 'editor'),
          tries: item.tries - 1
        }
      )

      return utils.cleanItem(item)
    }
    case FIND_UPDATE_ANSWER: {
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
