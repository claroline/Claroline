import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import invariant from 'invariant'

import {makeActionCreator} from '#/main/core/utilities/redux'
import {notBlank} from '#/main/core/validation'
import {tex} from '#/main/core/translation'

import {Cloze as component} from './editor.jsx'
import {makeId} from './../../utils/utils'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {utils} from './utils/utils'
import {select} from './selectors'
import {keywords as keywordsUtils} from './../../utils/keywords'

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
  removeAnswer: makeActionCreator(REMOVE_ANSWER, 'holeId', 'keywordId'),
  closePopover: makeActionCreator(CLOSE_POPOVER),
  updateAnswer: (holeId, keywordId, parameter, value) => {
    invariant(
      ['text', 'caseSensitive', 'feedback', 'score'].indexOf(parameter) > -1,
      'answer attribute is not valid'
    )
    invariant(holeId !== undefined, 'holeId is required')

    return {
      type: UPDATE_ANSWER,
      holeId, keywordId, parameter, value
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
    _text: utils.setEditorHtml(item.text, item.holes, item.solutions),
    solutions: item.solutions.map(solution => Object.assign({}, solution, {
      answers: solution.answers.map(keyword => Object.assign({}, keyword, {
        _id: makeId(),
        _deletable: solution.answers.length > 1
      }))
    }))
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
      // we need to check if every hole is mapped to a placeholder
      // if there is not placeholder, then remove the hole
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
      hole._multiple = !!hole.choices
      newItem._popover = true
      newItem._holeId = action.holeId

      return newItem
    }

    case ADD_HOLE: {
      const newItem = cloneDeep(item)

      const hole = {
        id: makeId(),
        feedback: '',
        size: 10,
        _score: 0,
        _multiple: false,
        placeholder: ''
      }

      const keyword = keywordsUtils.createNew()
      keyword.text = action.word
      keyword._deletable = false

      const solution = {
        holeId: hole.id,
        answers: [keyword]
      }

      newItem.holes.push(hole)
      newItem.solutions.push(solution)
      newItem._popover = true
      newItem._holeId = hole.id
      newItem._text = action.cb(utils.makeTinyHtml(hole, solution))
      newItem.text = utils.getTextWithPlacerHoldersFromHtml(newItem._text)

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

      updateHoleChoices(hole, getSolutionFromHole(newItem, hole))

      return newItem
    }

    case REMOVE_HOLE: {
      const newItem = cloneDeep(item)
      const holes = newItem.holes
      const solutions = newItem.solutions

      // Remove from holes list
      holes.splice(holes.findIndex(hole => hole.id === action.holeId), 1)

      // Remove from solutions
      const solution = solutions.splice(solutions.findIndex(solution => solution.holeId === action.holeId), 1)

      let bestAnswer
      if (solution && 0 !== solution.length) {
        // Retrieve the best answer
        bestAnswer = select.getBestAnswer(solution[0].answers)
      }

      // Replace hole with the best answer text
      const regex = new RegExp(`(\\[\\[${action.holeId}\\]\\])`, 'gi')
      newItem.text = newItem.text.replace(regex, bestAnswer ? bestAnswer.text : '')
      newItem._text = utils.setEditorHtml(newItem.text, newItem.holes, newItem.solutions)

      if (newItem._holeId && newItem._holeId === action.holeId) {
        newItem._popover = false
      }

      return newItem
    }

    case ADD_ANSWER: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, action.holeId)
      const solution = getSolutionFromHole(newItem, hole)

      const keyword = keywordsUtils.createNew()
      keyword._deletable = solution.answers.length > 0

      solution.answers.push(keyword)

      updateHoleChoices(hole, solution)

      return newItem
    }

    case UPDATE_ANSWER: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, action.holeId)
      const solution = getSolutionFromHole(newItem, hole)
      const answer = solution.answers.find(answer => answer._id === action.keywordId)

      answer[action.parameter] = action.value

      updateHoleChoices(hole, solution)

      return newItem
    }

    case REMOVE_ANSWER: {
      const newItem = cloneDeep(item)
      const hole = getHoleFromId(newItem, action.holeId)
      const solution = getSolutionFromHole(newItem, hole)
      const answers = solution.answers
      answers.splice(answers.findIndex(answer => answer._id === action.keywordId), 1)

      updateHoleChoices(hole, solution)

      answers.forEach(keyword => keyword._deletable = answers.length > 1)

      return newItem
    }

    case CLOSE_POPOVER: {
      const newItem = cloneDeep(item)
      newItem._popover = false

      return newItem
    }
  }
}

function updateHoleChoices(hole, holeSolution) {
  if (hole._multiple) {
    hole.choices = holeSolution.answers.map(answer => answer.text)
  } else {
    delete hole.choices
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

  if (notBlank(item.text, true)) {
    _errors.text = tex('cloze_empty_text_error')
  } else {
    if (item.holes.length === 0) {
      _errors.text = tex('cloze_must_contains_clozes_error')
    }
  }

  item.holes.forEach(hole => {
    const holeErrors = {}
    const solution = getSolutionFromHole(item, hole)

    if (notBlank(hole.size, true)) {
      holeErrors.size = tex('cloze_empty_size_error')
    }

    const keywordsErrors = keywordsUtils.validate(solution.answers, true, hole._multiple ? 2 : 1)
    if (!isEmpty(keywordsErrors)) {
      holeErrors.keywords = keywordsErrors
    }

    if (!isEmpty(holeErrors)) {
      _errors[hole.id] = holeErrors
    }
  })

  return _errors
}
