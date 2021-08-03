import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ValidationList} from '#/plugin/cursus/tools/trainings/validation/components/list'

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
      }
    ]}
  />

ValidationMain.propTypes = {
  path: T.string.isRequired
}

export {
  ValidationMain
}