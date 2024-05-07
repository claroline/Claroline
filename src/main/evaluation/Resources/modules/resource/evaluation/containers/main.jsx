import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors, reducer} from '#/main/evaluation/resource/evaluation/store'
import {ResourceEvaluations as ResourceEvaluationsComponent} from '#/main/evaluation/resource/evaluation/components/main'

const ResourceEvaluations = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      nodeId: resourceSelectors.id(state)
    })
  )(ResourceEvaluationsComponent)
)

export {
  ResourceEvaluations
}
