import {connect} from 'react-redux'
import {url} from '#/main/app/api'
import merge from 'lodash/merge'

import {withReducer} from '#/main/app/store/components/withReducer'
import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {reducer, selectors} from '#/plugin/open-badge/modals/transfer/store'
import {TransferModal as TransferModalComponent} from '#/plugin/open-badge/modals/transfer/components/transfer'
import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'

const TransferModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME)),
      data: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      reset() {
        dispatch(formActions.reset(selectors.STORE_NAME, merge({}, BadgeTypes.defaultProps, {}), true))
      },
      transfer(data) {
        dispatch(formActions.save(selectors.STORE_NAME, url(['apiv2_badge_transfer', {
          userFrom: data.sender.id,
          userTo: data.receiver.id
        }])))
      }
    })
  )(TransferModalComponent)
)

export {
  TransferModal
}
