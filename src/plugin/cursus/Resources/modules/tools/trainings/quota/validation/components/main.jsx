import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ValidationList} from '#/plugin/cursus/tools/trainings/quota/validation/components/list'
import {ValidationDetail} from '#/plugin/cursus/tools/trainings/quota/validation/containers/detail'

const ValidationMain = (props) =>
  <Routes
    path={`${props.path}/validation`}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
			    <ValidationList path={`${props.path}/validation`} />
        )
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.open(params.id),
        render: () => (
			    <ValidationDetail path={`${props.path}/validation`} />
        )
      }
    ]}
  />

ValidationMain.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired
}

export {
  ValidationMain
}