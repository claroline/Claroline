import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {CourseForm} from '#/plugin/cursus/course/containers/form'

import {selectors} from '#/plugin/cursus/course/store'
import {connect}   from 'react-redux'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

const CourseCreationComponent = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('catalog', {}, 'cursus'),
      target: props.path + '/catalog'
    }, {
      type: LINK_BUTTON,
      label: trans('new_course', {}, 'cursus'),
      target: '' // current page, no need to add a link
    }]}
    title={trans('trainings', {}, 'tools')}
    subtitle={trans('new_course', {}, 'cursus')}
    primaryAction="add"
    actions={[{
      name: 'add',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-plus',
      label: trans('add_course', {}, 'cursus'),
      target: `${props.path}/catalog/new`,
      group: trans('management'),
      primary: true
    }]}
  >
    <CourseForm
      path={props.path + '/catalog'}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

CourseCreationComponent.propTypes = {
  path: T.string.isRequired
}

const CourseCreation = connect(
  (state) => ({
    path: toolSelectors.path(state),
    course: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  })
)(CourseCreationComponent)

export {
  CourseCreation
}
