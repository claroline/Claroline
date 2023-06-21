import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {PasswordValidateTool as PasswordValidateComponent} from '#/main/authentication/administration/password-validate/components/tool'

const PasswordValidateTool =
  connect(
    (state) => ({
      path: toolSelectors.path(state),
    })
  )(PasswordValidateComponent)

export {
  PasswordValidateTool
}
