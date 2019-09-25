import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {toKey} from '#/main/core/scaffolding/text'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {refreshIdentifiers} from '#/plugin/exo/resources/quiz/utils'
import {EditorMain as EditorMainComponent} from '#/plugin/exo/resources/quiz/editor/components/main'
import {actions, selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {getStepSlug} from '#/plugin/exo/resources/quiz/editor/utils'

const EditorMain = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      formName: selectors.FORM_NAME,
      validating: formSelectors.validating(formSelectors.form(state, selectors.FORM_NAME)),
      pendingChanges: formSelectors.pendingChanges(formSelectors.form(state, selectors.FORM_NAME)),
      errors: formSelectors.errors(formSelectors.form(state, selectors.FORM_NAME)),

      quizId: selectors.quizId(state),
      quizType: selectors.quizType(state),
      workspace: resourceSelectors.workspace(state),
      numberingType: selectors.numberingType(state),
      hasExpectedAnswers: selectors.hasExpectedAnswers(state),
      score: selectors.score(state),
      tags: selectors.tags(state),
      randomPick: selectors.randomPick(state),
      steps: selectors.steps(state)
    }),
    (dispatch) => ({
      /**
       * Push the updated quiz data to the server.
       *
       * @param {string} quizId - the id of the quiz to save
       */
      save(quizId) {
        dispatch(actions.save(quizId))
      },

      /**
       * Change a quiz data value.
       *
       * @param {string} prop  - the path of the prop to update
       * @param {*}      value - the new value to set
       */
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
      },

      /**
       * Remove a step from the quiz.
       *
       * @param {string} stepId - the id of the step to delete
       */
      removeStep(stepId) {
        dispatch(actions.removeStep(stepId))
      },

      /**
       * Create a copy of a step and push it at the requested position.
       *
       * @param {object} stepId   - the id of the step to copy
       * @param {Array}  steps    - the list of existing steps
       * @param {object} position - the position to push the created step
       */
      copyStep(stepId, steps, position) {
        // create a copy of the step
        const pos = steps.findIndex(step => step.id === stepId)
        if (-1 !== pos) {
          const copy = cloneDeep(steps[pos])
          copy.id = makeId()

          // recalculate slug
          const title = copy.title || trans('step', {number: pos + 1}, 'quiz')
          copy.slug = getStepSlug(steps, toKey(title))

          // recalculate item ids
          if (copy.items) {
            Promise.all(
              copy.items.map(refreshIdentifiers)
            ).then(items => {
              copy.items = items
              dispatch(actions.copyStep(copy, position))
            })
          } else {
            dispatch(actions.copyStep(copy, position))
          }
        }
      },

      /**
       * Move an existing step to another position.
       *
       * @param {string} stepId   - the id of the step to move
       * @param {object} position - the new position of the step
       */
      moveStep(stepId, position) {
        dispatch(actions.moveStep(stepId, position))
      },

      /**
       * Create a copy of a item and push it at the requested position.
       *
       * @param {string} itemId   - the id of the item to copy
       * @param {object} position - the position to push the created item
       */
      copyItem(itemId, position) {
        dispatch(actions.copyItem(itemId, position))
      },

      /**
       * Move an existing item to another position.
       *
       * @param {string} itemId   - the id of the item to move
       * @param {object} position - the new position of the item
       */
      moveItem(itemId, position) {
        dispatch(actions.moveItem(itemId, position))
      }
    })
  )(EditorMainComponent)
)

export {
  EditorMain
}
