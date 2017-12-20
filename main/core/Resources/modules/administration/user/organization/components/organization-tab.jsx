import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'

import {Organization, OrganizationActions} from '#/main/core/administration/user/organization/components/organization.jsx'
import {Organizations, OrganizationsActions} from '#/main/core/administration/user/organization/components/organizations.jsx'

import {actions} from '#/main/core/administration/user/organization/actions'

const OrganizationTabActions = () =>
  <Routes
    routes={[
      {
        path: '/organizations',
        exact: true,
        component: OrganizationsActions
      }, {
        path: '/organizations/add',
        exact: true,
        component: OrganizationActions
      }, {
        path: '/organizations/:id',
        component: OrganizationActions
      }
    ]}
  />

const OrganizationTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/organizations',
        exact: true,
        component: Organizations
      }, {
        path: '/organizations/add',
        exact: true,
        onEnter: () => props.openForm(),
        component: Organization
      }, {
        path: '/organizations/:id',
        onEnter: (params) => props.openForm(params.id),
        component: Organization
      }
    ]}
  />

OrganizationTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const OrganizationTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('organizations.current', id))
    }
  })
)(OrganizationTabComponent)

export {
  OrganizationTabActions,
  OrganizationTab
}
