import {connect} from 'react-redux'

import {actions} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseSessions as CourseSessionsComponent} from '#/plugin/cursus/course/components/sessions'

const CourseSessions = connect(
  null,
  (dispatch) => ({
    reload(courseSlug) {
      dispatch(actions.open(courseSlug, true))
    }
  })
)(CourseSessionsComponent)

export {
  CourseSessions
}
