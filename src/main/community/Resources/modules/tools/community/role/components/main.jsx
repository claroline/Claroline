import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {RoleList} from '#/main/community/tools/community/role/containers/list'
import {RoleCreate} from '#/main/community/tools/community/role/containers/create'
import {RoleShow} from '#/main/community/tools/community/role/containers/show'
import {RoleEdit} from '#/main/community/tools/community/role/containers/edit'

const RoleMain = props =>
  <Routes
    path={`${props.path}/roles`}
    routes={[
      {
        path: '',
        exact: true,
        component: RoleList
      }, {
        path: '/new',
        component: RoleCreate,
        onEnter: () => props.new(props.contextData),
        disabled: !props.canCreate
      }, {
        path: '/:id',
        component: RoleShow,
        onEnter: (params) => props.open(params.id, props.contextData),
        exact: true
      }, {
        path: '/:id/edit',
        component: RoleEdit,
        onEnter: (params) => props.open(params.id, props.contextData)
      }
    ]}
  />

RoleMain.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  canCreate: T.bool.isRequired,

  open: T.func.isRequired,
  new: T.func.isRequired
}

export {
  RoleMain
}
