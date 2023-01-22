import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'
import {actions} from '#/plugin/competency/tools/evaluation/scale/store'
import {Scales} from '#/plugin/competency/tools/evaluation/scale/components/scales'
import {Scale} from '#/plugin/competency/tools/evaluation/scale/components/scale'

const ScaleTabComponent = props =>
  <Routes
    path={props.path+'/competencies'}
    routes={[
      {
        path: '/scales',
        exact: true,
        render: () => {
          const component = <Scales path={props.path} />

          return component
        }
      }, {
        path: '/scales/form/:id?',
        render: () => {
          const component = <Scale path={props.path} />

          return component
        },
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }
    ]}
  />

ScaleTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired
}

const ScaleTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    openForm(id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())

      dispatch(actions.open(competencySelectors.STORE_NAME + '.scales.current', defaultProps, id))
    },
    resetForm() {
      dispatch(actions.reset(competencySelectors.STORE_NAME + '.scales.current'))
    }
  })
)(ScaleTabComponent)

export {
  ScaleTab
}
