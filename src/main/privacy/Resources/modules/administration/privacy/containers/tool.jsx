import {connect} from 'react-redux'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {selectors} from '#/main/privacy/administration/privacy/store'

const PrivacyTool = connect(
  (state) => ({
    privacy: selectors.privacy(state)
  })
)(PrivacyToolComponent)

export {
  PrivacyTool
}
