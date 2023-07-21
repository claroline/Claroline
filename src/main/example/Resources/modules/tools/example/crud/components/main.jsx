import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {CrudList} from '#/main/example/tools/example/crud/components/list'
/*import {CrudCreate} from '#/main/example/tools/example/crud/containers/create'
import {CrudShow} from '#/main/example/tools/example/crud/containers/show'
import {CrudEdit} from '#/main/example/tools/example/crud/containers/edit'*/

const CrudMain = (props) =>
  <Routes
    path={props.path+'/crud'}
    routes={[
      {
        path: '/',
        render: () => <CrudList path={props.path} />,
        exact: true
      }/*, {
        path: '/new',
        component: CrudCreate,
        onEnter: props.new
      }, {
        path: '/:id',
        component: CrudShow,
        onEnter: (params) => props.open(params.id),
        exact: true
      }, {
        path: '/:id/edit',
        component: CrudEdit,
        onEnter: (params) => props.open(params.id)
      }*/
    ]}
  />

CrudMain.propTypes = {
  path: T.string.isRequired
}

export {
  CrudMain
}
