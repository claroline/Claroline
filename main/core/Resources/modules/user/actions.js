import {API_REQUEST} from '#/main/core/api/actions'

export const actions = {}

actions.changePassword = (user, plainPassword, onChange = () => {}) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(Object.assign({}, user, {plainPassword}))
    },
    success: (data, dispatch) => {
      onChange(data, dispatch)
    }
  }
})
