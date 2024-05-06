import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseForm} from '#/plugin/cursus/course/containers/form'

import {selectors} from '#/plugin/cursus/course/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const CourseEditComponent = (props) =>
  <CoursePage
    path={props.path}
    breadcrumb={[{
      type: LINK_BUTTON,
      label: trans('edition'),
      target: '' // current page, link is not needed
    }]}
    course={props.course}
  >
    <CourseForm
      path={props.path + '/catalog'}
      name={selectors.FORM_NAME}
    />
  </CoursePage>

CourseEditComponent.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  )
}

const CourseEdit = connect(
  (state) => ({
    path: toolSelectors.path(state),
    course: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  })
)(CourseEditComponent)

export {
  CourseEdit
}
