import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {CourseForm as CourseFormComponent} from '#/plugin/cursus/course/components/form'

const CourseForm = connect(
  (state, ownProps) =>({
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    course: formSelectors.data(formSelectors.form(state, ownProps.name))
  }),
  (dispatch) => ({
    update(name, prop, value) {
      dispatch(formActions.updateProp(name, prop, value))
    }
  })
)(CourseFormComponent)

export {
  CourseForm
}
