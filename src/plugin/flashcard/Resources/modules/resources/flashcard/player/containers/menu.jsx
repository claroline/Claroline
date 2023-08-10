import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {PlayerMenu as PlayerMenuComponent} from '#/plugin/flashcard/resources/flashcard/player/components/menu'
import {selectors} from '#/plugin/flashcard/resources/flashcard/store'

const PlayerMenu = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      overview: selectors.showOverview(state),
      showEndPage: selectors.showEndPage(state)
    })
  )(PlayerMenuComponent)
)

export {
  PlayerMenu
}
