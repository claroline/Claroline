import {createSelector} from 'reselect'
import isEmpty from 'lodash/isEmpty'
import {TYPE_QUIZ, TYPE_STEP} from './../enums'
import {tex, t} from '#/main/core/translation'

const quiz = state => state.quiz
const steps = state => state.steps
const items = state => state.items
const editor = state => state.editor

const saved = createSelector(editor, editor => editor.saved)
const validating = createSelector(editor, editor => editor.validating)
const currentObject = createSelector(editor, editor => editor.currentObject)
const openPanels = createSelector(editor, editor => editor.openPanels)
const quizOpenPanel = createSelector(openPanels, panels => panels[TYPE_QUIZ])
const openStepPanels = createSelector(openPanels, panels => panels[TYPE_STEP])

const stepList = createSelector(
  quiz,
  steps,
  (quiz, steps) => quiz.steps.map(id => steps[id])
)

const quizThumbnail = createSelector(
  quiz,
  currentObject,
  (quiz, current) => {
    return {
      id: quiz.id,
      title: t('parameters'),
      type: TYPE_QUIZ,
      active: quiz.id === current.id && current.type === TYPE_QUIZ,
      hasErrors: !isEmpty(quiz._errors)
    }
  }
)

const stepThumbnails = createSelector(
  stepList,
  currentObject,
  items,
  (steps, current, items) => steps.map((step, index) => {
    return {
      id: step.id,
      title: step.title || `${tex('step')} ${index + 1}`,
      type: TYPE_STEP,
      active: step.id === current.id && current.type === TYPE_STEP,
      hasErrors: !!step.items.find(id => !isEmpty(items[id]._errors))
    }
  })
)

const thumbnails = createSelector(
  quizThumbnail,
  stepThumbnails,
  (quiz, steps) => [quiz].concat(steps)
)

const currentObjectDeep = createSelector(
  currentObject,
  quiz,
  steps,
  items,
  (current, quiz, steps, items) => {
    if (current.type === TYPE_QUIZ) {
      return {
        type: TYPE_QUIZ,
        id: quiz.id
      }
    }

    return Object.assign({}, steps[current.id], {
      type: TYPE_STEP,
      items: steps[current.id].items.map(itemId => items[itemId])
    })
  }
)

const stepOpenPanel = createSelector(
  currentObject,
  openStepPanels,
  (current, panels) => {
    if (current.type === TYPE_STEP && panels[current.id] !== undefined) {
      return panels[current.id]
    }
    return false
  }
)

const nextObject = createSelector(
  currentObject,
  quiz,
  stepList,
  (current, quiz, steps) => {
    if (current.type === TYPE_QUIZ) {
      return current
    }

    if (steps.length <= 1) {
      return {
        id: quiz.id,
        type: TYPE_QUIZ
      }
    }

    const stepIndex = steps.findIndex(step => step.id === current.id)
    const nextIndex = stepIndex === 0 ? (stepIndex + 1) : (stepIndex - 1)

    return {
      id: steps[nextIndex].id,
      type: TYPE_STEP
    }
  }
)

const valid = createSelector(
  quiz,
  stepList,
  items,
  (quiz, steps, items) => {
    const hasQuizError = !isEmpty(quiz._errors)
    const hasStepError = !!steps.find(step => {
      return !!step.items.find(id => !isEmpty(items[id]._errors))
    })
    return !hasQuizError && !hasStepError
  }
)

export default {
  quiz,
  thumbnails,
  currentObjectDeep,
  quizOpenPanel,
  stepOpenPanel,
  nextObject,
  editor,
  valid,
  validating,
  saved,
  steps
}
