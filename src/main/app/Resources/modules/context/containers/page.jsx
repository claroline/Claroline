import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as toolActions} from '#/main/core/tool/store'

import {ContextPage as ContextPageComponent} from '#/main/app/context/components/page'
import {actions, reducer, selectors} from '#/main/app/context/store'

const ContextPage = connect(
  (state) => ({
    name: selectors.type(state),
    contextData: selectors.data(state)
  })
)(ContextPageComponent)

export {
  ContextPage
}
