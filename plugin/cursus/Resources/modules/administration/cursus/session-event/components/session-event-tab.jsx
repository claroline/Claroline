import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/session-event/store'
import {SessionEvent as SessionEventType} from '#/plugin/cursus/administration/cursus/prop-types'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'
import {SessionEvents} from '#/plugin/cursus/administration/cursus/session-event/components/session-events'
import {SessionEvent} from '#/plugin/cursus/administration/cursus/session-event/components/session-event'

const SessionEventTabComponent = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/events',
        exact: true,
        render: () => {
          const Events = (
            <SessionEvents path={props.path} />
          )

          return Events
        }
      }, {
        path: '/events/form/:id?',
        render: () => {
          const Event = (
            <SessionEvent path={props.path} />
          )

          return Event
        },
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

SessionEventTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const SessionEventTab = connect(
  null,
  (dispatch) => ({
    openForm(id = null) {
      if (id) {
        dispatch(actions.open(selectors.STORE_NAME + '.events.current', {}, id))
      } else {
        const defaultProps = cloneDeep(SessionEventType.defaultProps)
        set(defaultProps, 'id', makeId())
        dispatch(actions.open(selectors.STORE_NAME + '.events.current', defaultProps))

        dispatch(modalActions.showModal(MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-cubes',
          title: trans('select_a_session', {}, 'cursus'),
          confirmText: trans('select', {}, 'actions'),
          name: selectors.STORE_NAME + '.sessions.picker',
          definition: SessionList.definition,
          card: SessionList.card,
          fetch: {
            url: ['apiv2_cursus_session_list'],
            autoload: true
          },
          onlyId: false,
          handleSelect: (selected) => {
            dispatch(formActions.updateProp(selectors.STORE_NAME + '.events.current', 'meta.session.id', selected[0].id))
            dispatch(formActions.updateProp(selectors.STORE_NAME + '.events.current', 'registration.registrationType', selected[0].registration.eventRegistrationType))
            dispatch(formActions.updateProp(selectors.STORE_NAME + '.events.current', 'restrictions.dates', selected[0].restrictions.dates))
          }
        }))
      }
    },
    resetForm() {
      dispatch(actions.reset(selectors.STORE_NAME + '.events.current'))
    }
  })
)(SessionEventTabComponent)

export {
  SessionEventTab
}
