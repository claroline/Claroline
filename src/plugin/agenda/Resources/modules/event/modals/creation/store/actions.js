import merge from 'lodash/merge'

import {actions as formActions} from '#/main/app/content/form/store/actions'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {selectors} from '#/plugin/agenda/event/modals/creation/store/selectors'

// action creators
export const actions = {}

actions.startCreation = (context, type, currentUser) => (dispatch) => {
  // initialize the form with default values
  dispatch(formActions.resetForm(selectors.STORE_NAME, merge({}, EventTypes.defaultProps, {
    type: type,
    user: currentUser,
    workspace: context.type === 'workspace' ? {id: context.data.id} : null
  }), true))
}

actions.reset = () => formActions.resetForm(selectors.STORE_NAME, merge({}, EventTypes.defaultProps), true)
