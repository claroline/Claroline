import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {OrganizationCreate}  from '#/main/community/tools/community/organization/containers/create'
import {OrganizationEdit}  from '#/main/community/tools/community/organization/containers/edit'
import {OrganizationList} from '#/main/community/tools/community/organization/containers/list'
import {OrganizationShow} from '#/main/community/tools/community/organization/containers/show'

const OrganizationMain = props =>
  <Routes
    path={`${props.path}/organizations`}
    routes={[
      {
        path: '',
        component: OrganizationList,
        exact: true
      }, {
        path: '/new/:parent?',
        component: OrganizationCreate,
        onEnter: (params) => {
          let parent
          if (params.parent) {
            parent = props.organizations.find(organization => organization.id === params.parent)
          }

          props.new(parent)
        }
      }, {
        path: '/:id',
        component: OrganizationShow,
        onEnter: (params) => props.open(params.id),
        exact: true
      }, {
        path: '/:id/edit',
        component: OrganizationEdit,
        onEnter: (params) => props.open(params.id)
      }
    ]}
  />

OrganizationMain.propTypes = {
  path: T.string,
  organizations: T.array.isRequired,
  open: T.func.isRequired,
  new: T.func.isRequired
}

export {
  OrganizationMain
}
