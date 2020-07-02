import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {CursusTool as CursusToolComponent} from '#/plugin/cursus/administration/cursus/components/tool'

const CursusTool = withRouter(connect(
  (state) => ({
    parameters: selectors.parameters(state)
  }),
  (dispatch) => ({
    openParametersForm(defaultProps) {
      dispatch(formActions.resetForm(selectors.STORE_NAME + '.parametersForm', defaultProps, true))
    }
  })
)(CursusToolComponent))

export {
  CursusTool
}