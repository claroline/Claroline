import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {
  Parameters as ParametersType,
  Course as CourseType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {Courses} from '#/plugin/cursus/administration/cursus/course/components/courses'
import {CourseForm} from '#/plugin/cursus/administration/cursus/course/components/course-form'
import {actions} from '#/plugin/cursus/administration/cursus/course/store'

const CourseTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('create_course', {}, 'cursus')}
      target="/courses/form"
      primary={true}
    />
  </PageActions>

const CourseTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/courses',
        exact: true,
        component: Courses
      }, {
        path: '/courses/form/:id?',
        component: CourseForm,
        onEnter: (params) => props.openForm(props.parameters, params.id)
      }
    ]}
  />

CourseTabComponent.propTypes = {
  parameters: T.shape(ParametersType.propTypes),
  openForm: T.func.isRequired
}

const CourseTab = connect(
  (state) => ({
    parameters: selectors.parameters(state)
  }),
  (dispatch) => ({
    openForm(parameters, id = null) {
      const defaultProps = cloneDeep(CourseType.defaultProps)
      set(defaultProps, 'meta.defaultSessionDuration', parameters['session_default_duration'])

      dispatch(actions.open('courses.current', defaultProps, id))
    }
  })
)(CourseTabComponent)

export {
  CourseTabActions,
  CourseTab
}
