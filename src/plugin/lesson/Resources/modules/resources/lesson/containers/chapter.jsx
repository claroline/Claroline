import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Chapter as ChapterComponent} from '#/plugin/lesson/resources/lesson/components/chapter'
import {selectors} from '#/plugin/lesson/resources/lesson/store'

const Chapter = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    chapter: selectors.chapter(state),
    internalNotes: hasPermission('view_internal_notes', resourceSelectors.resourceNode(state)),
    treeData: selectors.treeData(state)
  })
)(ChapterComponent)

export {
  Chapter
}