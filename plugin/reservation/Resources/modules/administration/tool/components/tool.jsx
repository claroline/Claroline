import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {
  PageContainer,
  PageActions,
  PageAction,
  PageHeader
} from '#/main/core/layout/page'
import {
  RoutedPageContent
} from '#/main/core/layout/router'

import {actions} from '#/plugin/reservation/administration/resource/actions'
import {Resources} from '#/plugin/reservation/administration/resource/components/resources'
import {ResourceForm} from '#/plugin/reservation/administration/resource/components/resource-form'
import {MODAL_RESOURCE_TYPES} from '#/plugin/reservation/administration/resource-type/components/modal/resource-types-modal'

const Tool = props =>
  <PageContainer id="tool-page-container">
    <PageHeader
      title={trans('admin_resources_reservation', {}, 'tools')}
    >
      <PageActions>
        <PageAction
          id="resources-types-list"
          type="link"
          icon="fa fa-plus"
          label={trans('add_resource', {}, 'reservation')}
          target="/form"
        />

        {props.isAdmin &&
          <PageAction
            id="resources-types-list"
            type="modal"
            icon="fa fa-bars"
            label={trans('resource_types', {}, 'reservation')}
            modal={[MODAL_RESOURCE_TYPES, {}]}
          />
        }
      </PageActions>
    </PageHeader>

    <RoutedPageContent
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
  </PageContainer>

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