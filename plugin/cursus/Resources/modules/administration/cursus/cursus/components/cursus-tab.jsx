import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {Cursus as CursusType} from '#/plugin/cursus/administration/cursus/prop-types'
import {Cursus} from '#/plugin/cursus/administration/cursus/cursus/components/cursus'
import {CursusForm} from '#/plugin/cursus/administration/cursus/cursus/components/cursus-form'
import {actions} from '#/plugin/cursus/administration/cursus/cursus/store'

const CursusTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('create_cursus', {}, 'cursus')}
      target="/cursus/form"
      primary={true}
    />
  </PageActions>

const CursusTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/cursus',
        exact: true,
        component: Cursus
      }, {
        path: '/cursus/form/:id?',
        exact: true,
        component: CursusForm,
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

CursusTabComponent.propTypes = {
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const CursusTab = connect(
  null,
  (dispatch) => ({
    openForm(id = null) {
      const defaultProps = cloneDeep(CursusType.defaultProps)
      set(defaultProps, 'id', makeId())
      dispatch(actions.open('cursus.current', defaultProps, id))
    },
    resetForm() {
      dispatch(actions.reset('cursus.current'))
    }
  })
)(CursusTabComponent)

export {
  CursusTabActions,
  CursusTab
}
