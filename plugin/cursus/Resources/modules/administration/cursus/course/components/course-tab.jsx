import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'

import {Routes} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {
  Parameters as ParametersType,
  Course as CourseType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {Courses} from '#/plugin/cursus/administration/cursus/course/components/courses'
import {CourseForm} from '#/plugin/cursus/administration/cursus/course/components/course-form'
import {actions} from '#/plugin/cursus/administration/cursus/course/store'

const CourseTabComponent = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/courses',
        exact: true,
        render: () => {
          const CoursesList = (
            <Courses path={props.path} />
          )

          return CoursesList
        }
      }, {
        path: '/courses/form/:id?',
        render: () => {
          const Form = (
            <CourseForm path={props.path} />
          )

          return Form
        },
        onEnter: (params) => props.openForm(props.parameters, params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

CourseTabComponent.propTypes = {
  path: T.string.isRequired,
  parameters: T.shape(ParametersType.propTypes),
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const CourseTab = connect(
  (state) => ({
    parameters: selectors.parameters(state)
  }),
  (dispatch) => ({
    openForm(parameters, id = null) {
      const defaultProps = cloneDeep(CourseType.defaultProps)
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'meta.defaultSessionDuration', parameters['session_default_duration'])

      dispatch(actions.open(selectors.STORE_NAME + '.courses.current', defaultProps, id))
    },
    resetForm() {
      dispatch(actions.reset(selectors.STORE_NAME + '.courses.current'))
    }
  })
)(CourseTabComponent)

export {
  CourseTab
}
