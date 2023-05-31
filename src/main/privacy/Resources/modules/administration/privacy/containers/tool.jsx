import {connect} from 'react-redux'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/administration/privacy/components/tool'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {withReducer} from '#/main/app/store/components/withReducer'
import {reducer} from '#/main/privacy/administration/privacy/store/reducer'

const PrivacyTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      parameters: selectors.store(state)
    })
  )(PrivacyToolComponent)
)
export {
  PrivacyTool
}
