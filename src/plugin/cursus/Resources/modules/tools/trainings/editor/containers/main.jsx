import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool'
import {withReducer} from '#/main/app/store/components/withReducer'
import {reducer, selectors} from '#/plugin/cursus/tools/trainings/editor/store'
import {TrainingsEditor as TrainingsEditorComponent} from '#/plugin/cursus/tools/trainings/editor/components/main'

const TrainingsEditor = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      course: selectors.course(state),
      canEdit: hasPermission('edit', toolSelectors.toolData(state))
    })
  )(TrainingsEditorComponent)
)

export {
  TrainingsEditor
}
