import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {LINK_BUTTON} from '#/main/app/buttons'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseForm} from '#/plugin/cursus/course/containers/form'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogEdit = (props) =>
  <CoursePage
    path={props.path}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('edition'),
        target: '' // current page, link is not needed
      }
    ]}
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
