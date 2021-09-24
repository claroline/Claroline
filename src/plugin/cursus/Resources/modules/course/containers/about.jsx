import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CourseAbout as CourseAboutComponent} from '#/plugin/cursus/course/components/about'

const CourseAbout = withRouter(
  connect(
    null,
    (dispatch) => ({
      register(course, sessionId = null) {
        return dispatch(actions.register(course, sessionId))
      }
    })
  )(CourseAboutComponent)
)

export {
  CourseAbout
}
