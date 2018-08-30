import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {now, nowAdd} from '#/main/core/scaffolding/date'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/session/store'
import {
  Parameters as ParametersType,
  Session as SessionType
} from '#/plugin/cursus/administration/cursus/prop-types'
import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'
import {Sessions} from '#/plugin/cursus/administration/cursus/session/components/sessions'
import {Session} from '#/plugin/cursus/administration/cursus/session/components/session'

const SessionTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('create_session', {}, 'cursus')}
      target="/sessions/form"
      primary={true}
    />
  </PageActions>

const SessionTabComponent = (props) =>
  <Routes
    routes={[
      {
        path: '/sessions',
        exact: true,
        component: Sessions
      }, {
        path: '/sessions/form/:id?',
        component: Session,
        onEnter: (params) => props.openForm(props.parameters.session_default_duration, props.parameters.session_default_total, params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

SessionTabComponent.propTypes = {
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
        dispatch(actions.open('sessions.current', {}, id))
      } else {
        const defaultProps = cloneDeep(SessionType.defaultProps)
        const dates = [now(), nowAdd({days: duration ? duration : 1})]
        set(defaultProps, 'id', makeId())
        set(defaultProps, 'meta.total', total)
        set(defaultProps, 'restrictions.dates', dates)
        dispatch(actions.open('sessions.current', defaultProps))

        dispatch(modalActions.showModal(MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-tasks',
          title: trans('select_course_for_session_creation', {}, 'cursus'),
          confirmText: trans('select', {}, 'actions'),
          name: 'courses.picker',
          definition: CourseList.definition,
          card: CourseList.card,
          fetch: {
            url: ['apiv2_cursus_course_list'],
            autoload: true
          },
          handleSelect: (selected) => {
            dispatch(formActions.updateProp('sessions.current', 'meta.course.id', selected[0]))
          }
        }))
      }
    },
    resetForm() {
      dispatch(actions.reset('sessions.current'))
    }
  })
)(SessionTabComponent)

export {
  SessionTabActions,
  SessionTab
}
