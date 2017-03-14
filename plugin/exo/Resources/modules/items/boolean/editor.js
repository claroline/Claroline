import cloneDeep from 'lodash/cloneDeep'
import zipObject from 'lodash/zipObject'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {makeActionCreator, makeId} from './../../utils/utils'
import {tex} from './../../utils/translate'
import {notBlank} from './../../utils/validate'
import {Boolean as component} from './editor.jsx'

const UPDATE_CHOICE = 'UPDATE_CHOICE'
const UPDATE_CHOICES = 'UPDATE_CHOICES'

export const pairs = [
  {
    'id': '1',
    'labelA': tex('boolean_pair_true'),
    'labelB': tex('boolean_pair_false')
  },
  {
    'id': '2',
    'labelA': tex('boolean_pair_yes'),
    'labelB': tex('boolean_pair_no')
  }
]

export const actions = {
  updateChoice: makeActionCreator(UPDATE_CHOICE, 'id', 'property', 'value'),
  updateChoices: makeActionCreator(UPDATE_CHOICES, 'value')
}

function decorate(item) {
  const solutionsById = zipObject(
    item.solutions.map(solution => solution.id),
    item.solutions
  )
  const choicesWithSolutions = item.choices.map(
    choice => Object.assign({}, choice, {
      _score: solutionsById[choice.id].score,
      _feedback: solutionsById[choice.id].feedback || ''
    })
  )

  let decorated = Object.assign({}, item, {
    choices: choicesWithSolutions
  })

  return decorated
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
            data: pairs[0].labelA
          },
          {
            id: secondChoiceId,
            type: 'text/html',
            data: pairs[0].labelB
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
    case UPDATE_CHOICE: {
      const newItem = cloneDeep(item)
      const choiceIndex = newItem.choices.findIndex(choice => choice.id === action.id)
      const value = action.property === 'score' ? parseFloat(action.value) : action.value
      const decoratedName = action.property === 'data' ? 'data' : `_${action.property}`

      newItem.choices[choiceIndex][decoratedName] = value

      if (action.property === 'score' || action.property === 'feedback') {
        const solutionIndex = newItem.solutions.findIndex(
          solution => solution.id === action.id
        )
        newItem.solutions[solutionIndex][action.property] = value
      }

      return newItem
    }
    case UPDATE_CHOICES: {
      const newItem = cloneDeep(item)
      const pair = action.value
      newItem.choices.forEach((choice, index) => {
        choice.data = index === 0 ? pair.labelA : pair.labelB
        const solution = newItem.solutions.find(solution => solution.id === choice.id)
        solution.score = index === 0 ? 1 : 0
      })
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

  if (!item.choices.find(choice => choice._score > 0)) {
    errors.choices = tex('boolean_no_correct_answer_error')
  }

  return errors
}

export default {
  component,
  reduce,
  decorate,
  validate
}
