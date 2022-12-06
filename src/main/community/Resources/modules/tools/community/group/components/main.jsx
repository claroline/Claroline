import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {GroupList} from '#/main/community/tools/community/group/containers/list'
import {GroupShow} from '#/main/community/tools/community/group/containers/show'
import {GroupCreate} from '#/main/community/tools/community/group/containers/create'
import {GroupEdit} from '#/main/community/tools/community/group/containers/edit'

const GroupMain = props =>
  <Routes
    path={`${props.path}/groups`}
    routes={[
      {
        path: '',
        component: GroupList,
        exact: true
      }, {
        path: '/new',
        component: GroupCreate,
        onEnter: props.new,
        disabled: 'desktop' !== props.contextType || !props.canEdit
      }, {
        path: '/:id',
        component: GroupShow,
        onEnter: (params) => props.open(params.id),
        exact: true
      }, {
        path: '/:id/edit',
        component: GroupEdit,
        onEnter: (params) => props.open(params.id)
      }
    ]}
  />

GroupMain.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  canEdit: T.bool.isRequired,

  open: T.func.isRequired,
  new: T.func.isRequired
}

export {
  GroupMain
}
