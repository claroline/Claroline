import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {Organization}  from '#/main/core/administration/users/organization/components/organization'
import {Organizations} from '#/main/core/administration/users/organization/components/organizations'
import {actions, selectors} from '#/main/core/administration/users/organization/store'

const OrganizationTabActionsComponent = (props) =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_organization')}
      target={`${props.path}/organizations/form`}
      primary={true}
    />
  </PageActions>

OrganizationTabActionsComponent.propTypes = {
  path: T.string.isRequired
}

const OrganizationTabComponent = props =>
  <Routes
    path={props.path}
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
  path: T.string,
  openForm: T.func.isRequired,
  organizations: T.array.isRequired
}

const OrganizationTabActions = connect(
  state => ({
    path: toolSelectors.path(state)
  })
)(OrganizationTabActionsComponent)

const OrganizationTab = connect(
  state => ({
    path: toolSelectors.path(state),
    organizations: selectors.flattenedOrganizations(state)
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
