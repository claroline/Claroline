import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'
import {actions} from '#/plugin/competency/tools/evaluation/framework/store'
import {Frameworks} from '#/plugin/competency/tools/evaluation/framework/components/frameworks'
import {FrameworkForm} from '#/plugin/competency/tools/evaluation/framework/components/framework-form'
import {FrameworkImport} from '#/plugin/competency/tools/evaluation/framework/components/framework-import'
import {Framework} from '#/plugin/competency/tools/evaluation/framework/components/framework'
import {Competency} from '#/plugin/competency/tools/evaluation/framework/components/competency'
import {CompetencyAbility} from '#/plugin/competency/tools/evaluation/framework/components/competency-ability'
import {CompetencyAbilityChoice} from '#/plugin/competency/tools/evaluation/framework/components/competency-ability-choice'

const FrameworkTabComponent = (props) =>
  <Routes
    path={props.path+'/competencies'}
    routes={[
      {
        path: '/frameworks',
        exact: true,
        render: () => {
          const component = <Frameworks path={props.path} />

          return component
        }
      }, {
        path: '/frameworks/import',
        exact: true,
        component: FrameworkImport,
        onEnter: () => props.resetForm(competencySelectors.STORE_NAME + '.frameworks.import'),
        onLeave: () => props.resetForm(competencySelectors.STORE_NAME + '.frameworks.import')
      }, {
        path: '/frameworks/form/:id?',
        render: () => {
          const component = <FrameworkForm path={props.path} />

          return component
        },
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm(competencySelectors.STORE_NAME + '.frameworks.form')
      }, {
        path: '/frameworks/:id?',
        exact: true,
        render: () => {
          const component = <Framework path={props.path} />

          return component
        },
        onEnter: (params) => props.loadCurrent(params.id),
        onLeave: () => props.resetCurrent()
      }, {
        path: '/frameworks/:parentId/competency/:id?',
        component: Competency,
        onEnter: (params) => props.openCompetency(params.parentId, params.id),
        onLeave: () => props.resetCompetency()
      }, {
        path: '/frameworks/:competencyId/ability/:id?',
        component: CompetencyAbility,
        onEnter: (params) => props.openAbility(params.competencyId, params.id),
        onLeave: () => props.resetAbility()
      }, {
        path: '/frameworks/:competencyId/ability_choice',
        component: CompetencyAbilityChoice,
        onEnter: (params) => props.openAbilityChoice(params.competencyId),
        onLeave: () => props.resetAbility()
      }
    ]}
  />

FrameworkTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired,
  loadCurrent: T.func.isRequired,
  resetCurrent: T.func.isRequired,
  openCompetency: T.func.isRequired,
  resetCompetency: T.func.isRequired,
  openAbility: T.func.isRequired,
  openAbilityChoice: T.func.isRequired,
  resetAbility: T.func.isRequired
}

const FrameworkTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    openForm(id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())

      dispatch(actions.open(competencySelectors.STORE_NAME + '.frameworks.form', defaultProps, id))
    },
    resetForm(formName) {
      dispatch(actions.reset(formName))
    },
    loadCurrent(id) {
      dispatch(actions.loadCurrent(competencySelectors.STORE_NAME + '.frameworks.current', id))
    },
    resetCurrent() {
      dispatch(actions.resetCurrent(competencySelectors.STORE_NAME + '.frameworks.current'))
    },
    openCompetency(parentId, id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'parent', {'id': parentId})

      dispatch(actions.open(competencySelectors.STORE_NAME + '.frameworks.competency', defaultProps, id))
    },
    resetCompetency() {
      dispatch(actions.reset(competencySelectors.STORE_NAME + '.frameworks.competency'))
      dispatch(actions.invalidateList(competencySelectors.STORE_NAME + '.frameworks.competency.abilities.list'))
    },
    openAbility(competencyId, id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'competency', {'id': competencyId})
      set(defaultProps, 'ability', {'id': makeId()})

      dispatch(actions.openCompetencyAbility(competencySelectors.STORE_NAME + '.frameworks.competency_ability', defaultProps, id))
      dispatch(actions.open(competencySelectors.STORE_NAME + '.frameworks.competency', {}, competencyId))
    },
    openAbilityChoice(competencyId) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'competency', {'id': competencyId})

      dispatch(actions.openCompetencyAbility(competencySelectors.STORE_NAME + '.frameworks.competency_ability', defaultProps))
      dispatch(actions.open(competencySelectors.STORE_NAME + '.frameworks.competency', {}, competencyId))
    },
    resetAbility() {
      dispatch(actions.reset(competencySelectors.STORE_NAME + '.frameworks.competency'))
      dispatch(actions.reset(competencySelectors.STORE_NAME + '.frameworks.competency_ability'))
    }
  })
)(FrameworkTabComponent)

export {
  FrameworkTab
}
