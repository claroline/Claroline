import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'
import {QuotaList} from '#/plugin/cursus/tools/trainings/quota/components/list'
import {QuotaForm} from '#/plugin/cursus/tools/trainings/quota/containers/form'

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
      }, {
        path: '/new',
        exact: true,
        onEnter: () => props.openForm(null, QuotaTypes.defaultProps),
        render: () => (
          <QuotaForm path={`${props.path}/quota`} />
        )
      }, {
        path: '/:id',
        onEnter: (params = {}) => props.openForm(params.id),
        render: () => (
          <QuotaForm path={`${props.path}/quota`} />
        )
      }
    ]}
  />

QuotaMain.propTypes = {
  path: T.string.isRequired,
  open: T.func.isRequired,
  openForm: T.func.isRequired
}

export {
  QuotaMain
}