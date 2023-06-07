import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/account/privacy/components/tool'
import {selectors, reducer} from '#/main/privacy/account/privacy/store'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      parameters: selectors.store(state),
    })
  )(PrivacyToolComponent)
)

export {
  PrivacyTool
}
