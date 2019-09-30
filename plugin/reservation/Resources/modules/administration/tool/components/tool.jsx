import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'
import {PageFull} from '#/main/app/page/components/full'

import {actions} from '#/plugin/reservation/administration/resource/actions'
import {Resources} from '#/plugin/reservation/administration/resource/components/resources'
import {ResourceForm} from '#/plugin/reservation/administration/resource/components/resource-form'
import {MODAL_RESOURCE_TYPES} from '#/plugin/reservation/administration/resource-type/components/modal/resource-types-modal'

const Tool = props =>
  <PageFull
    title={trans('admin_resources_reservation', {}, 'tools')}
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_resource', {}, 'resource'),
        target: '/form'
      }, {
        name: 'resource_types',
        type: MODAL_BUTTON,
        icon: 'fa fa-bars',
        label: trans('resource_types', {}, 'reservation'),
        modal: [MODAL_RESOURCE_TYPES, {}],
        displayed: props.isAdmin
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: Resources
        }, {
          path: '/form/:id?',
          component: ResourceForm,
          onEnter: (params) => props.openForm(params.id || null)
        }
      ]}
    />
  </PageFull>

Tool.propTypes = {
  isAdmin: T.bool.isRequired,
  openForm: T.func.isRequired
}

const ReservationTool = connect(
  (state) => ({
    isAdmin: state.isAdmin
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.openForm('resourceForm', id))
    }
  })
)(Tool)

export {
  ReservationTool
}