import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {Organization}  from '#/main/core/administration/user/organization/components/organization.jsx'
import {Organizations} from '#/main/core/administration/user/organization/components/organizations.jsx'
import {actions}       from '#/main/core/administration/user/organization/actions'
import {select}        from '#/main/core/administration/user/organization/selectors'

const OrganizationTabActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="organizations.current"
      target={(organization, isNew) => isNew ?
        ['apiv2_organization_create'] :
        ['apiv2_organization_update', {id: organization.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/organizations/form'})}
      open={{
        icon: 'fa fa-plus',
        label: trans('add_organization'),
        action: '#/organizations/form'
      }}
      cancel={{
        action: () => navigate('/organizations')
      }}
    />
  </PageActions>

OrganizationTabActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const OrganizationTabActions = withRouter(OrganizationTabActionsComponent)

const OrganizationTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/organizations',
        exact: true,
        component: Organizations
      }, {
        path: '/organizations/form/:id?',
        onEnter: (params) => props.openForm(params.id),
        exact: true,
        component: Organization
      }, {
        path: '/organizations/form/parent/:parent',
        onEnter: (params) => {
          const parent = props.organizations.find(organization => organization.id === params.parent)
          props.openForm(null, parent)
        },
        component: Organization
      }
    ]}
  />

OrganizationTabComponent.propTypes = {
  openForm: T.func.isRequired,
  organizations: T.array.isRequired
}

const OrganizationTab = connect(
  state => ({
    organizations: select.flattenedOrganizations(state)
  }),
  dispatch => ({
    openForm(id = null, parent = null) {
      const defaultProps = {}
      if (parent) {
        defaultProps.parent = parent
      }
      dispatch(actions.open('organizations.current', id, defaultProps))
    }
  })
)(OrganizationTabComponent)

export {
  OrganizationTabActions,
  OrganizationTab
}
