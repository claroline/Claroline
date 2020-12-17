import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'

import {CourseForm as CourseFormComponent} from '#/plugin/cursus/course/components/form'

const CourseForm = connect(
  null,
  (dispatch) => ({
    update(name, prop, value) {
      dispatch(formActions.updateProp(name, prop, value))
    }
  })
)(CourseFormComponent)

export {
  CourseForm
}
