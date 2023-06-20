import {connect} from 'react-redux'

import {withReducer} from "#/main/app/store/components/withReducer";
import {PasswordValidateTool as PasswordValidateComponent} from '#/main/authentication/administration/password-validate/components/tool'
import {reducer, selectors} from '#/main/authentication/administration/password-validate/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const PasswordValidateTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      passwordValidate: selectors.passwordValidate(state),
      // form: formSelectors.form(state, selectors.STORE_NAME),
      // formData: selectors.parameters(state).passwordValidate
    })
  )(PasswordValidateComponent)
)

export {
  PasswordValidateTool
}
