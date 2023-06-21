import {connect} from 'react-redux'

import {withReducer} from "#/main/app/store/components/withReducer";
import {PasswordValidateTool as PasswordValidateComponent} from '#/main/authentication/administration/password-validate/components/tool'
import {reducer} from '#/main/authentication/administration/password-validate/store/reducer'
import {selectors} from '#/main/authentication/administration/password-validate/store/selectors'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const PasswordValidateTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      form: formSelectors.form(state, selectors.STORE_NAME),
    })
  )(PasswordValidateComponent)
)

export {
  PasswordValidateTool
}
