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
import {Choice as component} from './editor.jsx'

import {
  NUMBERING_NONE
} from './../../quiz/enums'

const UPDATE_PROP = 'UPDATE_PROP'
const UPDATE_CHOICE = 'UPDATE_CHOICE'
const ADD_CHOICE = 'ADD_CHOICE'
const REMOVE_CHOICE = 'REMOVE_CHOICE'

export const QCM_MULTIPLE = 'multiple'
export const QCM_SINGLE = 'single'

export const actions = {
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  updateChoice: makeActionCreator(UPDATE_CHOICE, 'id', 'property', 'value'),
  addChoice: makeActionCreator(ADD_CHOICE),
  removeChoice: makeActionCreator(REMOVE_CHOICE, 'id')
}

function decorate(item) {
  const solutionsById = zipObject(
    item.solutions.map(solution => solution.id),
    item.solutions
  )
  const choicesWithSolutions = item.choices.map(
    choice => Object.assign({}, choice, {
      _score: solutionsById[choice.id].score,
      _feedback: solutionsById[choice.id].feedback || '',
      _checked: false,
      _deletable: item.solutions.length > 2
    })
  )

  let decorated = Object.assign({}, item, {
    choices: choicesWithSolutions,
    numbering: item.numbering || NUMBERING_NONE
  })

  return setChoiceTicks(decorated)
}

function reduce(item = {}, action) {
  switch (action.type) {
    case ITEM_CREATE: {
      const firstChoiceId = makeId()
      const secondChoiceId = makeId()
      return decorate(Object.assign({}, item, {
        multiple: false,
        random: false,
        choices: [
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
            id: firstChoiceId,
            score: 1,
            feedback: ''
          },
          {
            id: secondChoiceId,
            score: 0,
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

      const newItem = cloneDeep(item)
      const property = set({}, action.property, value)
      setChoiceTicks(merge(newItem, property))

      if (newItem.score.type === SCORE_FIXED) {
        setScores(newItem, choice => choice._checked ? 1 : 0)
      }

      return newItem
    }

    case UPDATE_CHOICE: {
      const newItem = cloneDeep(item)
      const choiceIndex = newItem.choices.findIndex(choice => choice.id === action.id)
      const value = action.property === 'score' ? parseFloat(action.value) : action.value
      const decoratedName = action.property === 'data' ? 'data' : `_${action.property}`

      if (decoratedName === '_checked' && !item.multiple) {
        newItem.choices.forEach(choice => choice._checked = false)
      }

      newItem.choices[choiceIndex][decoratedName] = value

      if (newItem.score.type === SCORE_FIXED) {
        setScores(newItem, choice => choice._checked ? 1 : 0)
      }

      if (action.property === 'score' || action.property === 'feedback') {
        const solutionIndex = newItem.solutions.findIndex(
          solution => solution.id === action.id
        )
        newItem.solutions[solutionIndex][action.property] = value
      }

      return setChoiceTicks(newItem)
    }
    case ADD_CHOICE: {
      const newItem = cloneDeep(item)
      const choiceId = makeId()
      newItem.choices.push({
        id: choiceId,
        type: 'text/html',
        data: '',
        _feedback: '',
        _score: 0,
        _checked: false,
        _deletable: true
      })
      newItem.solutions.push({
        id: choiceId,
        feedback: '',
        score: 0
      })
      const deletable = newItem.choices.length > 2
      newItem.choices.forEach(choice => choice._deletable = deletable)
      return newItem
    }
    case REMOVE_CHOICE: {
      const newItem = cloneDeep(item)
      const choiceIndex = newItem.choices.findIndex(choice => choice.id === action.id)
      const solutionIndex = newItem.solutions.findIndex(solution => solution.id === action.id)
      newItem.choices.splice(choiceIndex, 1)
      newItem.solutions.splice(solutionIndex, 1)
      newItem.choices.forEach(choice => choice._deletable = newItem.choices.length > 2)
      return newItem
    }
  }
  return item
}

function validate(item) {
  const errors = {}

  if (item.choices.find(choice => notBlank(choice.data, true))) {
    errors.choices = tex('choice_empty_data_error')
  }

  if (item.score.type === SCORE_FIXED) {
    if (item.score.failure >= item.score.success) {
      set(errors, 'score.failure', tex('fixed_failure_above_success_error'))
      set(errors, 'score.success', tex('fixed_success_under_failure_error'))
    }

    if (!item.choices.find(choice => choice._score > 0)) {
      errors.choices = tex(
        item.multiple ?
          'fixed_score_choice_at_least_one_correct_answer_error' :
          'fixed_score_choice_no_correct_answer_error'
      )
    }
  } else {
    if (!item.choices.find(choice => choice._score > 0)) {
      errors.choices = tex(
        item.multiple ?
          'sum_score_choice_at_least_one_correct_answer_error' :
          'sum_score_choice_no_correct_answer_error'
      )
    }
  }

  return errors
}

function setScores(item, setter) {
  const scores = {}
  item.choices.forEach(choice => {
    choice._score = setter(choice)
    scores[choice.id] = choice._score
  })
  item.solutions.forEach(solution => solution.score = scores[solution.id])
}

function setChoiceTicks(item) {
  if (item.multiple) {
    item.choices.forEach(
      choice => choice._checked = choice._score > 0
    )
  } else {
    let max = 0
    let maxId = null

    item.choices.forEach(choice => {
      if (choice._score > max) {
        max = choice._score
        maxId = choice.id
      }
    })

    item.choices.forEach(choice =>
      choice._checked = max > 0 && choice.id === maxId
    )
  }

  return item
}

export default {
  component,
  reduce,
  decorate,
  validate
}
