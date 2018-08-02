import merge from 'lodash/merge'

import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {makeId} from '#/main/core/scaffolding/id'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/content/prop-types'
import {selectors} from '#/main/core/widget/content/modals/creation/store/selectors'

// action names
export const WIDGET_CONTENTS_LOAD = 'WIDGET_CONTENTS_LOAD'

// action creators
export const actions = {}

actions.loadContents = makeActionCreator(WIDGET_CONTENTS_LOAD, 'types')

actions.fetchContents = (context) => ({
  [API_REQUEST]: {
    url: ['apiv2_widget_available', {context: context}],
    success: (response, dispatch) => dispatch(actions.loadContents(response))
  }
})

/**
 * Starts the creation of the selected resource type.
 * It initializes the new resource node with the default & parent values.
 *
 * @param {string}      type
 * @param {string|null} source
 */
actions.startCreation = (type, source = null) => (dispatch) => {
  // initialize the form with default values
  dispatch(formActions.resetForm(selectors.FORM_NAME, merge({}, WidgetInstanceTypes.defaultProps, {
    id: makeId()
  }), true))

  dispatch(formActions.updateProp(selectors.FORM_NAME, 'type', type))
  dispatch(formActions.updateProp(selectors.FORM_NAME, 'source', source))
}

/**
 * Shortcut to update the content.
 *
 * @param {string} prop  - the name of the content's prop to update
 * @param {*}      value - the new value for the content's prop
 */
actions.update = (prop, value) => formActions.updateProp(selectors.FORM_NAME, prop, value)
