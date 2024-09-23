import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions, reducer, selectors} from '#/plugin/cursus/tools/trainings/presence/store'
import {PresenceMain as PresenceMainComponent} from '#/plugin/cursus/tools/trainings/presence/components/main'

const PresenceMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      currentUser: securitySelectors.currentUser(state)
    }),
    (dispatch) => ({
      getEventByCode(code = null) {
        dispatch(actions.getEventByCode(code))
      },
      resetEvent() {
        dispatch(actions.setCode(''))
        dispatch(actions.setCurrentEvent(null))
        dispatch(actions.setEventLoaded(false))
      }
    })
  )(PresenceMainComponent)
)

export {
  PresenceMain
}
