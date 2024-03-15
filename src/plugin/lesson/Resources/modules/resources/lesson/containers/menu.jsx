import {connect} from 'react-redux'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonMenu as LessonMenuComponent} from '#/plugin/lesson/resources/lesson/components/menu'

const LessonMenu = connect(
  (state) => ({
    overview: selectors.showOverview(state)
  })
)(LessonMenuComponent)

export {
  LessonMenu
}
