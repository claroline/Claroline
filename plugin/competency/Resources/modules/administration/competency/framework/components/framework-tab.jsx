import set from 'lodash/set'
import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router'

import {makeId} from '#/main/core/scaffolding/id'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {actions} from '#/plugin/competency/administration/competency/framework/store'
import {Frameworks} from '#/plugin/competency/administration/competency/framework/components/frameworks'
import {FrameworkForm} from '#/plugin/competency/administration/competency/framework/components/framework-form'
import {Framework} from '#/plugin/competency/administration/competency/framework/components/framework'
import {Competency} from '#/plugin/competency/administration/competency/framework/components/competency'
import {CompetencyAbility} from '#/plugin/competency/administration/competency/framework/components/competency-ability'
import {CompetencyAbilityChoice} from '#/plugin/competency/administration/competency/framework/components/competency-ability-choice'

const FrameworkTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('framework.create', {}, 'competency')}
      target="/frameworks/form"
      primary={true}
    />
  </PageActions>

const FrameworkTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/frameworks',
        exact: true,
        component: Frameworks
      }, {
        path: '/frameworks/form/:id?',
        component: FrameworkForm,
        onEnter: (params) => props.openForm(params.id),
        onLeave: () => props.resetForm()
      }, {
        path: '/frameworks/:id?',
        exact: true,
        component: Framework,
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
  null,
  (dispatch) => ({
    openForm(id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())

      dispatch(actions.open('frameworks.form', defaultProps, id))
    },
    resetForm() {
      dispatch(actions.reset('frameworks.form'))
    },
    loadCurrent(id) {
      dispatch(actions.loadCurrent('frameworks.current', id))
    },
    resetCurrent() {
      dispatch(actions.resetCurrent('frameworks.current'))
    },
    openCompetency(parentId, id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'parent', {'id': parentId})

      dispatch(actions.open('frameworks.competency', defaultProps, id))
    },
    resetCompetency() {
      dispatch(actions.reset('frameworks.competency')),
      dispatch(actions.invalidateList('frameworks.competency.abilities.list'))
    },
    openAbility(competencyId, id = null) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'competency', {'id': competencyId})
      set(defaultProps, 'ability', {'id': makeId()})

      dispatch(actions.openCompetencyAbility('frameworks.competency_ability', defaultProps, id))
      dispatch(actions.open('frameworks.competency', {}, competencyId))
    },
    openAbilityChoice(competencyId) {
      const defaultProps = {}
      set(defaultProps, 'id', makeId())
      set(defaultProps, 'competency', {'id': competencyId})

      dispatch(actions.openCompetencyAbility('frameworks.competency_ability', defaultProps))
      dispatch(actions.open('frameworks.competency', {}, competencyId))
    },
    resetAbility() {
      dispatch(actions.reset('frameworks.competency'))
      dispatch(actions.reset('frameworks.competency_ability'))
    }
  })
)(FrameworkTabComponent)

export {
  FrameworkTabActions,
  FrameworkTab
}
