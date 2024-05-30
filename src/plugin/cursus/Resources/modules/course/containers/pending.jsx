import {connect} from 'react-redux'

import {actions} from '#/plugin/cursus/course/store'
import {CoursePending as CoursePendingComponent} from '#/plugin/cursus/course/components/pending'

const CoursePending = connect(
  null,
  (dispatch) => ({
    updateUser(courseUser) {
      dispatch(actions.updateCourseUser(courseUser))
    },
    addUsers(courseId, users) {
      dispatch(actions.addCourseUsers(courseId, users))
    },
    moveUsers(courseId, targetId, courseUsers) {
      dispatch(actions.moveCourseUsers(courseId, targetId, courseUsers))
    }
  })
)(CoursePendingComponent)

export {
  CoursePending
}
