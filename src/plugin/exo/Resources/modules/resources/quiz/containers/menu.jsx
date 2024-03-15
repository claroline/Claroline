import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {QuizMenu as QuizMenuComponent} from '#/plugin/exo/resources/quiz/components/menu'
import {selectors} from '#/plugin/exo/resources/quiz/store'

const QuizMenu = /*withRouter(*/
  connect(
    (state) => ({
      hasOverview: selectors.hasOverview(state),
    })
  )(QuizMenuComponent)
/*)*/

export {
  QuizMenu
}
