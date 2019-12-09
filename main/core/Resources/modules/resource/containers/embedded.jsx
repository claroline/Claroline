import {connect} from 'react-redux'

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
  })
)(ResourceEmbeddedComponent)

export {
  ResourceEmbedded
}
