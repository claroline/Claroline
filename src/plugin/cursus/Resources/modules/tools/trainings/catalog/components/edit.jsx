import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseForm} from '#/plugin/cursus/course/containers/form'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogEdit = (props) =>
  <CoursePage
    path={props.path}
    course={props.course}
  >
    <CourseForm
      path={props.path+'/catalog'}
      name={selectors.FORM_NAME}
    />
  </CoursePage>

CatalogEdit.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  )
}

export {
  CatalogEdit
}
