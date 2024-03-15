import {connect} from 'react-redux'

// the component to connect
import {ResourceRestrictions as ResourceRestrictionsComponent} from '#/main/core/resource/components/restrictions'
// the store to use
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions, selectors} from '#/main/core/resource/store'

const ResourceRestrictions = connect(
  (state) => ({
    authenticated: securitySelectors.isAuthenticated(state),
    managed: selectors.managed(state),
    resourceNode: selectors.resourceNode(state),
    accessErrors: selectors.accessErrors(state)
  }),
  (dispatch) => ({
    dismiss() {
      dispatch(actions.dismissRestrictions())
    },
    checkAccessCode(resourceNode, code) {
      dispatch(actions.checkAccessCode(resourceNode, code))
    }
  })
)(ResourceRestrictionsComponent)

export {
  ResourceRestrictions
}
