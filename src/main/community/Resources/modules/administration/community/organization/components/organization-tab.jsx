import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'

import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {Organization}  from '#/main/community/administration/community/organization/components/organization'
import {Organizations} from '#/main/community/administration/community/organization/components/organizations'
import {actions, selectors} from '#/main/community/administration/community/organization/store'

const OrganizationTabComponent = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('organizations'),
      target: `${props.path}/organizations`
    }]}
    subtitle={trans('organizations')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_organization'),
        target: `${props.path}/organizations/form`,
        primary: true
      }
    ]}
  >
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
  </ToolPage>

OrganizationTabComponent.propTypes = {
  path: T.string,
  openForm: T.func.isRequired,
  organizations: T.array.isRequired
}

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
      dispatch(actions.open(selectors.FORM_NAME, id, defaultProps))
    }
  })
)(OrganizationTabComponent)

export {
  OrganizationTab
}
