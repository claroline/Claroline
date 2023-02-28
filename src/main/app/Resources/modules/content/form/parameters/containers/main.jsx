import {connect} from 'react-redux'

import {FormParameters as FormParametersComponent} from '#/main/app/content/form/parameters/components/main'
import {actions as formActions} from '#/main/app/content/form/store'

const FormParameters = connect(
  null,
  (dispatch) => ({
    update(formName, path, prop, value) {
      dispatch(formActions.updateProp(formName, path, prop, value))
    }
  })
)(FormParametersComponent)

export {
  FormParameters
}
