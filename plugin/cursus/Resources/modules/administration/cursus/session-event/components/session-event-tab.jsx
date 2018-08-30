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
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {actions} from '#/plugin/cursus/administration/cursus/session-event/store'
import {SessionEvent as SessionEventType} from '#/plugin/cursus/administration/cursus/prop-types'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'
import {SessionEvents} from '#/plugin/cursus/administration/cursus/session-event/components/session-events'
import {SessionEvent} from '#/plugin/cursus/administration/cursus/session-event/components/session-event'

const SessionEventTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('create_session_event', {}, 'cursus')}
      target="/events/form"
      primary={true}
    />
  </PageActions>

const SessionEventTabComponent = (props) =>
  <Routes
    routes={[
      {
        path: '/events',
        exact: true,
        component: SessionEvents
      }, {
        path: '/events/form/:id?',
        component: SessionEvent,
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

SessionEventTabComponent.propTypes = {
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const SessionEventTab = connect(
  null,
  (dispatch) => ({
    openForm(id = null) {
      if (id) {
        dispatch(actions.open('events.current', {}, id))
      } else {
        const defaultProps = cloneDeep(SessionEventType.defaultProps)
        set(defaultProps, 'id', makeId())
        dispatch(actions.open('events.current', defaultProps))

        dispatch(modalActions.showModal(MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-cubes',
          title: trans('select_a_session', {}, 'cursus'),
          confirmText: trans('select', {}, 'actions'),
          name: 'sessions.picker',
          definition: SessionList.definition,
          card: SessionList.card,
          fetch: {
            url: ['apiv2_cursus_session_list'],
            autoload: true
          },
          onlyId: false,
          handleSelect: (selected) => {
            dispatch(formActions.updateProp('events.current', 'meta.session.id', selected[0].id))
            dispatch(formActions.updateProp('events.current', 'registration.registrationType', selected[0].registration.eventRegistrationType))
            dispatch(formActions.updateProp('events.current', 'restrictions.dates', selected[0].restrictions.dates))
          }
        }))
      }
    },
    resetForm() {
      dispatch(actions.reset('events.current'))
    }
  })
)(SessionEventTabComponent)

export {
  SessionEventTabActions,
  SessionEventTab
}
