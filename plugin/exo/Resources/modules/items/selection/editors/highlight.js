import cloneDeep from 'lodash/cloneDeep'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {tex} from '#/main/core/translation'

import {makeId} from '../../../utils/utils'
import {utils} from '../utils/utils'

const HIGHLIGHT_ADD_SELECTION = 'HIGHLIGHT_ADD_SELECTION'
const HIGHLIGHT_UPDATE_ANSWER = 'HIGHLIGHT_UPDATE_ANSWER'
const HIGHLIGHT_REMOVE_SELECTION = 'HIGHLIGHT_REMOVE_SELECTION'
const HIGHLIGHT_ADD_COLOR = 'HIGHLIGHT_ADD_COLOR'
const HIGHLIGHT_EDIT_COLOR = 'HIGHLIGHT_EDIT_COLOR'
const HIGHLIGHT_ADD_ANSWER = 'HIGHLIGHT_ADD_ANSWER'
const HIGHLIGHT_REMOVE_ANSWER = 'HIGHLIGHT_REMOVE_ANSWER'
const HIGHLIGHT_REMOVE_COLOR = 'HIGHLIGHT_REMOVE_COLOR'

export const actions = {
  highlightAddColor: makeActionCreator(HIGHLIGHT_ADD_COLOR),
  highlightEditColor: makeActionCreator(HIGHLIGHT_EDIT_COLOR, 'colorId', 'colorCode'),
  highlightAddAnswer: makeActionCreator(HIGHLIGHT_ADD_ANSWER, 'selectionId'),
  highlightAddSelection: makeActionCreator(HIGHLIGHT_ADD_SELECTION, 'begin', 'end'),
  highlightRemoveSelection: makeActionCreator(HIGHLIGHT_REMOVE_SELECTION, 'selectionId'),
  highlightUpdateAnswer: makeActionCreator(HIGHLIGHT_UPDATE_ANSWER, 'parameter', 'value', '_answerId'),
  highlightRemoveAnswer: makeActionCreator(HIGHLIGHT_REMOVE_ANSWER, '_answerId'),
  highlightRemoveColor: makeActionCreator(HIGHLIGHT_REMOVE_COLOR, 'colorId')
}

export function reduce(item = {}, action) {
  switch (action.type) {
    case HIGHLIGHT_ADD_COLOR: {
      const colors = cloneDeep(item.colors)

      colors.push({
        id: makeId(),
        code: '#'+(Math.random()*0xFFFFFF<<0).toString(16),
        _autoOpen: true
      })

      return Object.assign({}, item, {colors})
    }
    case HIGHLIGHT_EDIT_COLOR: {
      const colors = cloneDeep(item.colors)
      const color = colors.find(color => color.id === action.colorId)
      color.code = action.colorCode

      return Object.assign({}, item, {colors})
    }
    case HIGHLIGHT_ADD_SELECTION: {
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
        answers: [{
          score: 1,
          colorId: item.colors[0].id,
          _answerId: makeId()
        }]
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
    case HIGHLIGHT_REMOVE_SELECTION: {
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
          _text: utils.makeTextHtml(item.text, selections)
        }
      )

      return utils.cleanItem(item)
    }
    case HIGHLIGHT_ADD_ANSWER: {
      const solutions = cloneDeep(item.solutions)
      const solution = solutions.find(solution => solution.selectionId === action.selectionId)
      solution.answers.push({score: 0, colorId: item.colors[0].id, _answerId: makeId()})

      return Object.assign({}, item, {solutions})
    }
    case HIGHLIGHT_UPDATE_ANSWER: {
      const solutions = cloneDeep(item.solutions)
      let answer = null

      solutions.forEach(solution => {
        if (!answer) answer = solution.answers.find(answer => answer._answerId === action._answerId)
      })

      answer[action.parameter] = action.value

      return Object.assign({}, item, {solutions})
    }
    case HIGHLIGHT_REMOVE_ANSWER: {
      const solutions = cloneDeep(item.solutions)
      const solution = solutions.find(solution => solution.selectionId === item._selectionId)
      solution.answers.splice(solution.answers.findIndex(answer => answer._answerId === action._answerId), 1)

      return Object.assign({}, item, {solutions})
    }
    case HIGHLIGHT_REMOVE_COLOR: {
      const colors = cloneDeep(item.colors)
      colors.splice(colors.findIndex(color => color.id === action.colorId), 1)

      const solutions = cloneDeep(item.solutions)
      solutions.forEach(solution => {
        solution.answers.splice(solution.answers.findIndex(answer => answer.colorId === action.colorId))
      })

      return Object.assign({}, item, {colors, solutions})
    }
  }
  return item
}

export function validate(item) {
  const _errors = {}
  let hasValidAnswers = false

  item.solutions.forEach(solution => {
    if (solution.answers.length === 0) {
      _errors.text = tex('selection_solution_missing_colors_error')
    }

    solution.answers.forEach(answer => {
      if (answer.score > 0) {
        hasValidAnswers = true
      }
    })
  })

  if (!hasValidAnswers) {
    _errors.text = tex('selection_text_must_have_valid_answers_error')
  }

  if (item._selectionId) {
    const solution = item.solutions.find(solution => solution.selectionId === item._selectionId)

    let hasModalValidAnswer = false
    const selectedColors = []

    if (solution && solution.answers) {
      solution.answers.forEach(answer => {
        if (answer.score > 0) {
          hasModalValidAnswer = true
        }
        selectedColors.push(answer.colorId)
      })
    }

    if (!hasModalValidAnswer) {
      _errors.solutions = tex('selection_must_have_valid_answers_errors')
    }

    if (hasDuplicates(selectedColors)) {
      _errors.solutions = tex('selection_answers_must_use_different_colors_errors ')
    }
  }

  const allowedColors = item.colors.map(color => color.code)

  if (hasDuplicates(allowedColors)) {
    _errors.colors = tex('selection_colors_must_be_differents')
  }


  return _errors
}

function hasDuplicates(array) {
  return (new Set(array)).size !== array.length
}
