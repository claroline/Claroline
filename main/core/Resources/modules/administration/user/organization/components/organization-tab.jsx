import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {Organization}  from '#/main/core/administration/user/organization/components/organization.jsx'
import {Organizations} from '#/main/core/administration/user/organization/components/organizations.jsx'
import {actions}       from '#/main/core/administration/user/organization/actions'

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
        label: t('add_organization'),
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
