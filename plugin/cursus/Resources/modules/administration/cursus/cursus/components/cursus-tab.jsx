import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import set from 'lodash/set'

import {Routes} from '#/main/app/router'
import {makeId} from '#/main/core/scaffolding/id'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/cursus/store'
import {Cursus} from '#/plugin/cursus/administration/cursus/cursus/components/cursus'
import {CursusForm} from '#/plugin/cursus/administration/cursus/cursus/components/cursus-form'

const CursusTabComponent = props =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/cursus',
        exact: true,
        render: () => {
          const Details = (
            <Cursus path={props.path} />
          )

          return Details
        }
      }, {
        path: '/cursus/form/:id?',
        exact: true,
        render: () => {
          const Form = (
            <CursusForm path={props.path} />
          )

          return Form
        },
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }, {
        path: '/cursus/form/parent/:parentId',
        render: () => {
          const ParentForm = (
            <CursusForm path={props.path} />
          )

          return ParentForm
        },
        onEnter: (params) => props.openForm(null, params.parentId),
        onLeave: () => props.resetForm()
      }
    ]}
  />

CursusTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const CursusTab = connect(
  null,
  (dispatch) => ({
    openForm(id = null, parentId = null) {
      const defaultProps = {} //cloneDeep(CursusType.defaultProps)
      set(defaultProps, 'id', makeId())
      dispatch(actions.open(selectors.STORE_NAME + '.cursus.current', defaultProps, id, parentId))
    },
    resetForm() {
      dispatch(actions.reset(selectors.STORE_NAME + '.cursus.current'))
    }
  })
)(CursusTabComponent)

export {
  CursusTab
}
