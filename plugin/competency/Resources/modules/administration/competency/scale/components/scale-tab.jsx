import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {actions} from '#/plugin/competency/administration/competency/scale/store'
import {Scales} from '#/plugin/competency/administration/competency/scale/components/scales'
import {Scale} from '#/plugin/competency/administration/competency/scale/components/scale'

const ScaleTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('scale.create', {}, 'competency')}
      target="/scales/form"
      primary={true}
    />
  </PageActions>

const ScaleTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/scales',
        exact: true,
        component: Scales
      }, {
        path: '/scales/form/:id?',
        component: Scale,
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

ScaleTabComponent.propTypes = {
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const ScaleTab = connect(
  null,
  (dispatch) => ({
    openForm(id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())

      dispatch(actions.open('scales.current', defaultProps, id))
    },
    resetForm() {
      dispatch(actions.reset('scales.current'))
    }
  })
)(ScaleTabComponent)

export {
  ScaleTabActions,
  ScaleTab
}
