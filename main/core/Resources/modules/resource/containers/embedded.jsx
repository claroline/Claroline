import {connect} from 'react-redux'

import {actions} from '#/main/core/resource/store'
// the component to connect
import {ResourceEmbedded as ResourceEmbeddedComponent} from '#/main/core/resource/components/embedded'
// the store to use
import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as securitySelectors} from '#/main/app/security/store'

const ResourceEmbedded = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    impersonated: securitySelectors.isImpersonated(state),
    config: configSelectors.config(state)
  }),
  (dispatch) => ({
    open(resourceId) {
      dispatch(actions.fetchNode(resourceId))
    },
    close(resourceSlug) {
      dispatch(actions.closeResource(resourceSlug, true))
    }
  })
)(ResourceEmbeddedComponent)

export {
  ResourceEmbedded
}
