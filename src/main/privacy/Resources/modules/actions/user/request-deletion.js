import {constants, constants as actionConstants, declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'

export default declareAction((users, refresher, path, currentUser) => ({
  name: 'request_deletion',
  type: ASYNC_BUTTON,
  label: trans('request_deletion', {}, 'actions'),
  request: {
    url: ['apiv2_request_account_deletion'],
    request: {method: 'POST', type: actionConstants.ACTION_SEND},
    messages: {
      pending: {
        title: trans('send.pending.title', {}, 'alerts'),
        message: trans('send.pending.message', {}, 'alerts')
      },
      success: {
        title: trans('send.success.title', {}, 'alerts'),
        message: trans('send.success.message', {}, 'alerts')
      }
    }
  },
  confirm: trans('delete_account_message', {}, 'privacy'),
  displayed: currentUser && currentUser.id === users[0].id,
  dangerous: true,
  scope: ['object'],
  set: [constants.ACTION_SET_ADVANCED],
  title: trans('request_deletion', {}, 'privacy'),
  description: trans('request_deletion_desc', {}, 'privacy'),
  labelShort: trans('send', {}, 'actions')
}))
