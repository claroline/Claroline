import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {Message as MessageTypes} from '#/plugin/message/prop-types'
import {reducer, selectors} from '#/plugin/message/modals/message/store'
import {MessageModal as MessageModalComponent} from '#/plugin/message/modals/message/components/message'

const MessageModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isAdmin: securitySelectors.isAdmin(state),
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset(receivers) {
        dispatch(formActions.resetForm(selectors.STORE_NAME, merge({}, MessageTypes.defaultProps, {
          receivers: receivers
        }), true))
      },

      send(callback) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_message_create'])).then((response) => {
          if (callback) {
            callback(response)
          }
        })
      }
    })
  )(MessageModalComponent)
)

export {
  MessageModal
}