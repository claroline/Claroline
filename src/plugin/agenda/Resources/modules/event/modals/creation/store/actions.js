import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import moment from 'moment'

import {now} from '#/main/app/intl/date'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {Event as EventTypes} from '#/plugin/agenda/prop-types'
import {selectors} from '#/plugin/agenda/event/modals/creation/store/selectors'

// action creators
export const actions = {}

actions.startCreation = (baseProps = {}, type, currentUser, context = null) => (dispatch) => {
  // initialize the form with default values
  const start = baseProps.start || now(false)
  const end = moment(start, 'YYYY-MM-DDThh:mm:ss')
  // default event duration to 1 hour
  end.add(1, 'h')

  dispatch(formActions.resetForm(selectors.STORE_NAME, merge({}, EventTypes.defaultProps, baseProps, {
    start: start,
    end: end.format('YYYY-MM-DDThh:mm:ss'),
    workspace: !isEmpty(context) ? context : null,
    meta: {
      type: type,
      creator: merge({}, currentUser)
    }
  }), true))
}

actions.reset = () => formActions.resetForm(selectors.STORE_NAME, merge({}, EventTypes.defaultProps), true)
