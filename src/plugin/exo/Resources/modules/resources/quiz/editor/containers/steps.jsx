import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {toKey} from '#/main/core/scaffolding/text'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors as editorSelectors} from '#/main/core/resource/editor/store'

import {refreshIdentifiers} from '#/plugin/exo/resources/quiz/utils'
import {QuizEditorSteps as QuizEditorStepsComponent} from '#/plugin/exo/resources/quiz/editor/components/steps'
import {selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {
  addStep,
  copyStep,
  getStepSlug,
  moveStep,
  removeStep,
  addItem,
  copyItem,
  moveItem,
  removeItem
} from '#/plugin/exo/resources/quiz/editor/utils'

const QuizEditorSteps = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      errors: formSelectors.errors(formSelectors.form(state, editorSelectors.STORE_NAME)),

      numberingType: selectors.numberingType(state),
      questionNumberingType: selectors.questionNumberingType(state),
      hasExpectedAnswers: selectors.hasExpectedAnswers(state),
      score: selectors.score(state),
      steps: selectors.steps(state)
    }),
    (dispatch) => ({
      update(prop, value) {
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.'+prop, value))
      },

      addStep(steps = []) {
        const updatedSteps = addStep(steps)
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', updatedSteps))

        // return slug for redirection
        return updatedSteps[updatedSteps.length - 1].slug
      },
      copyStep(steps, stepId, position) {
        // create a copy of the step
        const pos = steps.findIndex(step => step.id === stepId)
        if (-1 !== pos) {
          const copy = cloneDeep(steps[pos])
          copy.id = makeId()

          // recalculate slug
          const stepTitle = copy.title ? toKey(copy.title) : ''
          const title = stepTitle || toKey(trans('step', {number: pos + 1}, 'quiz'))
          copy.slug = getStepSlug(steps, title)

          // recalculate item ids
          if (copy.items) {
            Promise.all(
              copy.items.map(refreshIdentifiers)
            ).then(items => {
              copy.items = items
              dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', copyStep(steps, copy, position)))
            })
          } else {
            dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', copyStep(steps, copy, position)))
          }
        }
      },
      moveStep(steps, stepId, position) {
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', moveStep(steps, stepId, position)))
      },
      removeStep(steps, stepId) {
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', removeStep(steps, stepId)))
      },

      addItem(steps, stepId, item) {
        const updatedSteps = addItem(steps, stepId, item)
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', updatedSteps))

        // return id for redirection (id has been generated in the creation modal)
        return item.id
      },
      copyItem(steps, item, position) {
        refreshIdentifiers(item).then(copy => {
          dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', copyItem(steps, copy, position)))
        })
      },
      moveItem(steps, itemId, position) {
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', moveItem(steps, itemId, position)))
      },
      removeItem(steps, itemId) {
        dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.steps', removeItem(steps, itemId)))
      }
    })
  )(QuizEditorStepsComponent)
)

export {
  QuizEditorSteps
}
