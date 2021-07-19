import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {QuotaList} from '#/plugin/cursus/tools/trainings/quota/components/list'

const QuotaMain = (props) =>
  <Routes
    path={`${props.path}/quota`}
    routes={[
      {
        path: '/',
        exact: true,
        render: () => (
			    <QuotaList path={`${props.path}/quota`} />
        )
      }
    ]}
  />

QuotaMain.propTypes = {
  path: T.string.isRequired
}

export {
  QuotaMain
}