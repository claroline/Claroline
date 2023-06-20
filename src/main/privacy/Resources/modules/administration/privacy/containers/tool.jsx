import {connect} from 'react-redux'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {selectors, reducer} from '#/main/privacy/administration/privacy/store'
import {withReducer} from '#/main/app/store/components/withReducer'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      parameters: selectors.store(state),
      isAdmin: selectors.getAdminRole(state)
    })
  )(PrivacyToolComponent)
)

export {
  PrivacyTool
}
