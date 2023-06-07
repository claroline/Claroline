import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'
import {PrivacyTool as PrivacyToolComponent} from '#/main/privacy/account/privacy/components/tool'
import {selectors, reducer} from '#/main/privacy/account/privacy/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

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
