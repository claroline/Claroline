import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonMenu as LessonMenuComponent} from '#/plugin/lesson/resources/lesson/components/menu'

const LessonMenu = connect(
  (state) => ({
    lesson: selectors.lesson(state),
    tree: selectors.treeData(state),
    editable: hasPermission('edit', resourceSelectors.resourceNode(state))
  })
)(LessonMenuComponent)

export {
  LessonMenu
}
