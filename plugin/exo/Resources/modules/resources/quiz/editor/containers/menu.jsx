import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {toKey} from '#/main/core/scaffolding/text'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {refreshIdentifiers} from '#/plugin/exo/resources/quiz/utils'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {EditorMenu as EditorMenuComponent} from '#/plugin/exo/resources/quiz/editor/components/menu'
import {actions, selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {getStepSlug} from '#/plugin/exo/resources/quiz/editor/utils'

const EditorMenu = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      steps: selectors.steps(state),
      validating: formSelectors.validating(formSelectors.form(state, selectors.FORM_NAME)),
      errors: formSelectors.errors(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      addStep(steps = []) {
        // generate slug now to be able to redirect
        const title = trans('step', {number: steps.length + 1}, 'quiz')
        const slug = getStepSlug(steps, toKey(title))

        dispatch(actions.addStep({
          slug: slug
        }))

        // return slug for redirection
        return slug
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
      }
    })
  )(EditorMenuComponent)
)

export {
  EditorMenu
}
