import {Cloze as component} from './editor.jsx'
import {makeActionCreator, makeId} from './../../utils/utils'
import cloneDeep from 'lodash/cloneDeep'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {utils} from './utils/utils'
import {notBlank} from './../../utils/validate'
import set from 'lodash/set'
import get from 'lodash/get'
import invariant from 'invariant'
import flatten from 'lodash/flatten'
import {tex} from './../../utils/translate'

const UPDATE_TEXT = 'UPDATE_TEXT'
const ADD_HOLE = 'ADD_HOLE'
const OPEN_HOLE = 'OPEN_HOLE'
const UPDATE_HOLE = 'UPDATE_HOLE'
const ADD_ANSWER = 'ADD_ANSWER'
const UPDATE_ANSWER = 'UPDATE_ANSWER'
const SAVE_HOLE = 'SAVE_HOLE'
const REMOVE_HOLE = 'REMOVE_HOLE'
const REMOVE_ANSWER = 'REMOVE_ANSWER'
const CLOSE_POPOVER = 'CLOSE_POPOVER'

export const actions = {
  updateText: makeActionCreator(UPDATE_TEXT, 'text'),
  addHole: makeActionCreator(ADD_HOLE, 'word', 'cb'),
  openHole: makeActionCreator(OPEN_HOLE, 'holeId'),
  updateHole: makeActionCreator(UPDATE_HOLE, 'holeId', 'parameter', 'value'),
  addAnswer: makeActionCreator(ADD_ANSWER, 'holeId'),
  saveHole: makeActionCreator(SAVE_HOLE),
  removeHole: makeActionCreator(REMOVE_HOLE, 'holeId'),
  removeAnswer: makeActionCreator(REMOVE_ANSWER, 'text', 'caseSensitive'),
  closePopover: makeActionCreator(CLOSE_POPOVER),
  updateAnswer: (holeId, parameter, oldText, caseSensitive, value) => {
    invariant(
      ['text', 'caseSensitive', 'feedback', 'score'].indexOf(parameter) > -1,
      'answer attribute is not valid'
    )
    invariant(holeId !== undefined, 'holeId is required')
    invariant(oldText !== undefined, 'oldText is required')
    invariant(caseSensitive !== undefined, 'caseSensitive is required')

    return {
      type: UPDATE_ANSWER,
      holeId, parameter, oldText, caseSensitive, value
    }
  }
}

export default {
  component,
  reduce,
  validate,
  decorate
}

function decorate(item) {
  return Object.assign({}, item, {
    _text: utils.setEditorHtml(item.text, item.solutions)
  })
}

function reduce(item = {}, action) {
  switch (action.type) {
    case ITEM_CREATE: {
      return Object.assign({}, item, {
        text: '',
        holes: [],
        solutions: [],
        _text: ''
      })
    }
    case UPDATE_TEXT: {
      item = Object.assign({}, item, {
        text: utils.getTextWithPlacerHoldersFromHtml(action.text),
        _text: action.text
      })

      const holesToRemove = []
      //we need to check if every hole is mapped to a placeholder
      //if there is not placeholder, then remove the hole
      item.holes.forEach(hole => {
        if (item.text.indexOf(`[[${hole.id}]]`) < 0) {
          holesToRemove.push(hole.id)
        }
      })

      if (holesToRemove) {
        const holes = cloneDeep(item.holes)
        const solutions = cloneDeep(item.solutions)
        holesToRemove.forEach(toRemove => {
          holes.splice(holes.findIndex(hole => hole.id === toRemove), 1)
          solutions.splice(solutions.findIndex(solution => solution.holeId === toRemove), 1)
        })
        item = Object.assign({}, item, {holes, solutions})
      }

      return item
    }
    case OPEN_HOLE: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, action.holeId)
      hole._multiple = hole.choices ? true: false
      newItem._popover = true
      newItem._holeId = action.holeId

      return newItem
    }
    case UPDATE_ANSWER: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, newItem._holeId)
      const solution = getSolutionFromHole(newItem, hole)
      const answer = solution.answers.find(
        answer => answer.text === action.oldText && answer.caseSensitive === action.caseSensitive
      )

      answer[action.parameter] = action.value

      return newItem
    }
    case ADD_ANSWER: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, newItem._holeId)
      const solution = getSolutionFromHole(newItem, hole)

      solution.answers.push({
        text: '',
        caseSensitive: false,
        feedback: '',
        score: 1
      })

      return newItem
    }
    case UPDATE_HOLE: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, newItem._holeId)

      if (['size', '_multiple'].indexOf(action.parameter) > -1) {
        hole[action.parameter] = action.value
      } else {
        throw `${action.parameter} is not a valid hole attribute`
      }

      const choices = hole._multiple ?
         flatten(newItem.solutions.map(solution => solution.answers.map(answer => answer.text))): []

      if (choices.length > 0) hole.choices = choices

      return newItem
    }
    case ADD_HOLE: {
      const newItem = cloneDeep(item)

      const hole = {
        id: makeId(),
        feedback: '',
        size: 10,
        _score: 0,
        placeholder: ''
      }

      const solution = {
        holeId: hole.id,
        answers: [{
          text: action.word,
          caseSensitive: false,
          feedback: '',
          score: 1
        }]
      }

      newItem.holes.push(hole)
      newItem.solutions.push(solution)
      newItem._popover = true
      newItem._holeId = hole.id
      newItem._text = action.cb(utils.makeTinyHtml(solution))
      newItem.text = utils.getTextWithPlacerHoldersFromHtml(newItem._text)

      return newItem
    }
    case REMOVE_HOLE: {
      const newItem = cloneDeep(item)
      const holes = newItem.holes
      const solutions = newItem.solutions
      holes.splice(holes.findIndex(hole => hole.id === action.holeId), 1)
      solutions.splice(solutions.findIndex(solution => solution.holeId === action.holeId), 1)
      const regex = new RegExp(`(\\[\\[${action.holeId}\\]\\])`, 'gi')
      newItem.text = newItem.text.replace(regex, '')
      newItem._text = utils.setEditorHtml(newItem.text, newItem.solutions)

      return newItem
    }
    case REMOVE_ANSWER: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, item._holeId)
      const solution = getSolutionFromHole(newItem, hole)
      const answers = solution.answers
      answers.splice(answers.findIndex(answer => answer.text === action.text && answer.caseSensitive === action.caseSensitive), 1)

      return newItem
    }
    case CLOSE_POPOVER: {
      const newItem = cloneDeep(item)
      newItem._popover = false

      return newItem
    }
  }
}

function getHoleFromId(item, holeId) {
  return item.holes.find(hole => hole.id === holeId)
}

function getSolutionFromHole(item, hole)
{
  return item.solutions.find(solution => solution.holeId === hole.id)
}

function validate(item) {
  const _errors = {}

  item.holes.forEach(hole => {
    const solution = getSolutionFromHole(item, hole)
    let hasPositiveValue = false

    solution.answers.forEach((answer, key) => {
      if (notBlank(answer.text, true)) {
        set(_errors, `answers.answer.${key}.text`, tex('cloze_empty_word_error'))
      }

      if (notBlank(answer.score, true) && answer.score !== 0) {
        set(_errors, `answers.answer.${key}.score`, tex('cloze_empty_score_error'))
      }

      if (answer.score > 0) hasPositiveValue = true
    })

    if (hasDuplicates(solution.answers)) {
      set(_errors, 'answers.duplicate', tex('cloze_duplicate_answers'))
    }

    if (!hasPositiveValue) {
      set(_errors, 'answers.value', tex('solutions_requires_positive_answer'))
    }

    if (hole._multiple && solution.answers.length < 2) {
      set(_errors, 'answers.multiple', tex('cloze_multiple_answers_required'))
    }

    if (notBlank(hole.size, true)) {
      set(_errors, 'answers.size', tex('cloze_empty_size_error'))
    }

    if (!_errors.text) {
      const answerErrors = get(_errors, 'answers.answer')
      if (answerErrors && answerErrors.length > 0) {
        _errors.text = tex('cloze_holes_errors')
      }
    }
  })

  if (notBlank(item.text, true)) {
    _errors.text = tex('cloze_empty_text_error')
  }

  if (!_errors.text) {
    if (item.holes.length === 0) {
      _errors.text = tex('cloze_must_contains_clozes_error')
    }
  }

  return _errors
}

function hasDuplicates(answers) {
  let hasDuplicates = false
  answers.forEach(answer => {
    let count = 0
    answers.forEach(check => {
      if (answer.text === check.text && answer.caseSensitive === check.caseSensitive) {
        count++
      }
    })
    if (count > 1) hasDuplicates = true
  })

  return hasDuplicates
}
