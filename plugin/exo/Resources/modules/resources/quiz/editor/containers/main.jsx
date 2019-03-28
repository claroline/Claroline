import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {EditorMain as EditorMainComponent} from '#/plugin/exo/resources/quiz/editor/components/main'
import {actions, selectors} from '#/plugin/exo/resources/quiz/editor/store'

const EditorMain = withRouter(
  connect(
    (state) => ({
      formName: selectors.FORM_NAME,
      validating: formSelectors.validating(formSelectors.form(state, selectors.FORM_NAME)),
      pendingChanges: formSelectors.pendingChanges(formSelectors.form(state, selectors.FORM_NAME)),
      errors: formSelectors.errors(formSelectors.form(state, selectors.FORM_NAME)),

      quizId: formSelectors.value(formSelectors.form(state, selectors.FORM_NAME), 'id'),
      workspace: resourceSelectors.workspace(state),
      numberingType: selectors.numberingType(state),
      tags: selectors.tags(state),
      randomPick: selectors.randomPick(state),
      steps: selectors.steps(state)
    }),
    (dispatch, ownProps) => ({
      /**
       * Push the updated quiz data to the server.
       *
       * @param {string} quizId - the id of the quiz to save
       */
      save(quizId) {
        dispatch(formActions.save(selectors.FORM_NAME, ['exercise_update', {id: quizId}]))
      },

      /**
       * Change quiz a quiz data value.
       *
       * @param {string} prop  - the path of the prop to update
       * @param {*}      value - the new value to set
       */
      update(prop, value) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
      },

      /**
       * Create a new step in the quiz.
       */
      addStep() {
        // generate id now to be able to redirect to new step
        const stepId = makeId()

        dispatch(actions.addStep({id: stepId}))

        ownProps.history.push(`/edit/${stepId}`)
      },

      /**
       * Remove a step from the quiz.
       *
       * @param {string} stepId - the id of the step to delete
       */
      removeStep(stepId) {
        dispatch(actions.removeStep(stepId))

        if (`/edit/${stepId}` === ownProps.history.location.pathname) {
          ownProps.history.push('/edit')
        }
      },

      /**
       * Create a copy of a step and push it at the requested position.
       *
       * @param {string} stepId   - the id of the step to copy
       * @param {object} position - the position to push the created step
       */
      copyStep(stepId, position) {
        dispatch(actions.copyStep(stepId, position))
      },

      /**
       * Move an existing step to another position.
       *
       * @param {string} stepId   - the id of the step to move
       * @param {object} position - the new position of the step
       */
      moveStep(stepId, position) {
        dispatch(actions.moveStep(stepId, position))
      }
    })
  )(EditorMainComponent)
)

export {
  EditorMain
}
