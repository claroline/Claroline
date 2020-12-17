import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {PlayerMenu as PlayerMenuComponent} from '#/plugin/scorm/resources/scorm/player/components/menu'
import {selectors} from '#/plugin/scorm/resources/scorm/store'
import {flattenScos} from '#/plugin/scorm/resources/scorm/utils'

const PlayerMenu = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      scorm: selectors.scorm(state),
      scos: flattenScos(selectors.scos(state))
    })
  )(PlayerMenuComponent)
)

export {
  PlayerMenu
}
