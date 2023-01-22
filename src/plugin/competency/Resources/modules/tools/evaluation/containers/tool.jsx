import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {CompetencyTool as CompetencyToolComponent} from '#/plugin/competency/tools/evaluation/components/tool'
import {reducer, selectors} from '#/plugin/competency/tools/evaluation/store'

const CompetencyTool = withRouter(withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(CompetencyToolComponent)
))

export {
  CompetencyTool
}