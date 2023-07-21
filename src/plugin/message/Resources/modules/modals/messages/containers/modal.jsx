import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {MessagesModal as MessagesModalComponent} from '#/plugin/message/modals/messages/components/modal'
import {actions, reducer, selectors} from '#/plugin/message/modals/messages/store'

const MessagesModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      loaded: selectors.loaded(state),
      results: selectors.results(state)
    }),
    (dispatch) => ({
      getMessages() {
        dispatch(actions.getMessages())
      }
    })
  )(MessagesModalComponent)
)

export {
  MessagesModal
}
