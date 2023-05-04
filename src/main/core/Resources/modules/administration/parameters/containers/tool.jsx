import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'

import {ParametersTool as ParametersToolComponent} from '#/main/core/administration/parameters/components/tool'

const ParametersTool = withRouter(connect()(ParametersToolComponent))

export {
  ParametersTool
}
