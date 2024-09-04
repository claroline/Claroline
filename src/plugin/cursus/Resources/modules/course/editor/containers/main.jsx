import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as toolSelectors} from '#/main/core/tool'
import {reducer, selectors} from '#/plugin/cursus/course/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {CourseEditor as CourseEditorComponent} from '#/plugin/cursus/course/editor/components/main'

const CourseEditor = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      contextType: toolSelectors.contextType(state),
      course: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      update(name, prop, value) {
        dispatch(formActions.updateProp(name, prop, value))
      }
    })
  )(CourseEditorComponent)
)

export {
  CourseEditor
}
