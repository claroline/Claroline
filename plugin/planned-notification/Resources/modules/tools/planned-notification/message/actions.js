import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/core/data/form/actions'

export const actions = {}

actions.open = (formName, id = null, defaultProps) => {
  if (id) {
    return {
      [API_REQUEST]: {
        url: ['apiv2_plannednotificationmessage_get', {id}],
        success: (data, dispatch) => dispatch(formActions.resetForm(formName, data, false))
      }
    }
  } else {
    return formActions.resetForm(formName, defaultProps, true)
  }
}

actions.sendMessages = (messages, users) => ({
  [API_REQUEST]: {
    url: ['apiv2_plannednotificationmessage_messages_send'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        messages: messages.map(message => message.id),
        users: users
      })
    }
  }
})