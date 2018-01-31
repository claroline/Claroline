import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {Role}    from '#/main/core/administration/user/role/components/role.jsx'
import {Roles}   from '#/main/core/administration/user/role/components/roles.jsx'
import {actions} from '#/main/core/administration/user/role/actions'

const RoleTabActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="roles.current"
      target={(role, isNew) => isNew ?
        ['apiv2_role_create'] :
        ['apiv2_role_update', {id: role.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/roles/form'})}
      open={{
        icon: 'fa fa-plus',
        label: t('add_role'),
        action: '#/roles/form'
      }}
      cancel={{
        action: () => navigate('/roles')
      }}
    />
  </PageActions>

RoleTabActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const RoleTabActions = withRouter(RoleTabActionsComponent)

const RoleTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/roles',
        exact: true,
        component: Roles
      }, {
        path: '/roles/form/:id?',
        component: Role,
        onEnter: (params) => props.openForm(params.id || null)
      }
    ]}
  />

RoleTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const RoleTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('roles.current', id))
    }
  })
)(RoleTabComponent)

export {
  RoleTabActions,
  RoleTab
}
