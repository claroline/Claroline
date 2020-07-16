import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store'

import {actions, selectors} from '#/plugin/cursus/tools/cursus/catalog/store'
import {CourseSessions as CourseSessionsComponent} from '#/plugin/cursus/course/components/sessions'

const CourseSessions = connect(
  null,
  (dispatch) => ({
    reload(courseSlug) {
      dispatch(actions.open(courseSlug, true))
    },
    invalidateList() {
      dispatch(listActions.invalidateData(selectors.STORE_NAME+'.courseSessions'))
    }
  })
)(CourseSessionsComponent)

export {
  CourseSessions
}
