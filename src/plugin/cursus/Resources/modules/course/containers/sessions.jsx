import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseSessions as CourseSessionsComponent} from '#/plugin/cursus/course/components/sessions'

const CourseSessions = withRouter(
  connect(
    null,
    (dispatch) => ({
      reload(courseSlug) {
        return dispatch(actions.open(courseSlug, true))
      }
    })
  )(CourseSessionsComponent)
)

export {
  CourseSessions
}
