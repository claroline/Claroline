import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {PlayerMain as PlayerMainComponent} from '#/plugin/exo/resources/quiz/player/components/main'
import {selectors} from '#/plugin/exo/resources/quiz/player/store'

const PlayerMain = withRouter(
  connect(
    (state) => ({
      numberingType: selectors.numberingType(state),
      steps: selectors.steps(state)
    })
  )(PlayerMainComponent)
)

export {
  PlayerMain
}
