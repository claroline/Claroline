import {connect} from 'react-redux'

import {actions} from '#/plugin/cursus/tools/trainings/catalog/store'
import {CoursePending as CoursePendingComponent} from '#/plugin/cursus/course/components/pending'

const CoursePending = connect(
  null,
  (dispatch) => ({
    addUsers(courseId, users) {
      dispatch(actions.addCourseUsers(courseId, users))
    },
    inviteUsers(courseId, users) {
      dispatch(actions.inviteUsers(courseId, users))
    },
    moveUsers(courseId, targetId, courseUsers) {
      dispatch(actions.moveCourseUsers(courseId, targetId, courseUsers))
    }
  })
)(CoursePendingComponent)

export {
  CoursePending
}
