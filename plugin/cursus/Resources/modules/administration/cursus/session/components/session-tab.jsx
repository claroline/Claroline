import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {actions as formActions} from '#/main/app/content/form/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {now, nowAdd} from '#/main/app/intl/date'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/session/store'
import {
  Parameters as ParametersType,
  Session as SessionType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'
import {Sessions} from '#/plugin/cursus/administration/cursus/session/components/sessions'
import {Session} from '#/plugin/cursus/administration/cursus/session/components/session'

const SessionTabComponent = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/sessions',
        exact: true,
        render: () => {
          const SessionList = (
            <Sessions path={props.path} />
          )

          return SessionList
        }
      }, {
        path: '/sessions/form/:id?',
        render: () => {
          const SessionForm = (
            <Session path={props.path} />
          )

          return SessionForm
        },
        onEnter: (params) => props.openForm(props.parameters.cursus.session_default_duration, props.parameters.cursus.session_default_total, params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

SessionTabComponent.propTypes = {
  path: T.string.isRequired,
  parameters: T.shape(ParametersType.propTypes),
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const SessionTab = connect(
  (state) => ({
    parameters: selectors.parameters(state)
  }),
  (dispatch) => ({
    openForm(duration, total, id = null) {
      if (id) {
        dispatch(actions.open(selectors.STORE_NAME + '.sessions.current', {}, id))
      } else {
        const defaultProps = cloneDeep(SessionType.defaultProps)
        const dates = [now(false), nowAdd({days: duration ? duration : 1})]
        set(defaultProps, 'id', makeId())
        set(defaultProps, 'meta.total', total)
        set(defaultProps, 'restrictions.dates', dates)
        dispatch(actions.open(selectors.STORE_NAME + '.sessions.current', defaultProps))

        dispatch(modalActions.showModal(MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-tasks',
          title: trans('select_course_for_session_creation', {}, 'cursus'),
          confirmText: trans('select', {}, 'actions'),
          name: selectors.STORE_NAME + '.courses.picker',
          definition: CourseList.definition,
          card: CourseList.card,
          fetch: {
            url: ['apiv2_cursus_course_list'],
            autoload: true
          },
          handleSelect: (selected) => {
            dispatch(formActions.updateProp(selectors.STORE_NAME + '.sessions.current', 'meta.course.id', selected[0]))
          }
        }))
      }
    },
    resetForm() {
      dispatch(formActions.reset(selectors.STORE_NAME + '.sessions.current'))
    }
  })
)(SessionTabComponent)

export {
  SessionTab
}
