import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {QuizMenu as QuizMenuComponent} from '#/plugin/exo/resources/quiz/components/menu'

const QuizMenu = withRouter(
  connect(
    (state) => ({
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(QuizMenuComponent)
)

export {
  QuizMenu
}
