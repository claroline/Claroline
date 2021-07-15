import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {SendingModal as SendingModalComponent} from '#/plugin/announcement/resources/announcement/modals/sending/components/modal'
import {actions, reducer, selectors} from '#/plugin/announcement/resources/announcement/modals/sending/store'

const SendingModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(

  )(SendingModalComponent)
)

export {
  SendingModal
}
