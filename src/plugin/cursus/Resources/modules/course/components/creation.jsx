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
      target: props.path,
      displayed: 'desktop' === props.contextType
    }, {
      type: LINK_BUTTON,
      label: trans('new_course', {}, 'cursus'),
      target: '' // current page, no need to add a link
    }]}
    title={trans('trainings', {}, 'tools')}
    subtitle={trans('new_course', {}, 'cursus')}
  >
    <CourseForm
      contextType={props.contextType}
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

CourseCreationComponent.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired
}

const CourseCreation = connect(
  (state) => ({
    path: toolSelectors.path(state),
    course: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
    contextType: toolSelectors.contextType(state)
  })
)(CourseCreationComponent)

export {
  CourseCreation
}
