import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {ChapterList as ChapterListComponent} from '#/plugin/lesson/resources/lesson/components/list'
import {selectors} from '#/plugin/lesson/resources/lesson/store'

const ChapterList = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    lesson: selectors.lesson(state),
    internalNotes: hasPermission('view_internal_notes', resourceSelectors.resourceNode(state))
  })
)(ChapterListComponent)

export {
  ChapterList
}
