import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ScormSummary as ScormSummaryComponent} from '#/plugin/scorm/resources/scorm/components/summary'
import {selectors} from '#/plugin/scorm/resources/scorm/store'
import {flattenScos} from '#/plugin/scorm/resources/scorm/utils'

const ScormSummary = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      scorm: selectors.scorm(state),
      scos: flattenScos(selectors.scos(state))
    })
  )(ScormSummaryComponent)
)

export {
  ScormSummary
}
