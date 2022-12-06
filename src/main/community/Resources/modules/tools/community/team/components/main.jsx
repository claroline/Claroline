import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {TeamList} from '#/main/community/tools/community/team/containers/list'
import {TeamShow} from '#/main/community/tools/community/team/containers/show'
import {TeamCreate} from '#/main/community/tools/community/team/containers/create'
import {TeamEdit} from '#/main/community/tools/community/team/containers/edit'

const TeamMain = props =>
  <Routes
    path={`${props.path}/teams`}
    routes={[
      {
        path: '',
        component: TeamList,
        exact: true
      }, {
        path: '/new',
        component: TeamCreate,
        onEnter: () => props.new(props.contextData),
        disabled: !props.canCreate
      }, {
        path: '/:id',
        component: TeamShow,
        onEnter: (params) => props.open(params.id),
        exact: true
      }, {
        path: '/:id/edit',
        component: TeamEdit,
        onEnter: (params) => props.open(params.id)
      }
    ]}
  />

TeamMain.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  canCreate: T.bool.isRequired,

  open: T.func.isRequired,
  new: T.func.isRequired
}

export {
  TeamMain
}
