import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'

import {
  Competency as CompetencyType,
  CompetencyAbility as CompetencyAbilityType
} from '#/plugin/competency/administration/competency/prop-types'

const CompetencyAbilityComponent = (props) =>
  <section className="resource-section">
    <h2>{props.new ? trans('ability.creation', {}, 'competency') : trans('ability.edition', {}, 'competency')}</h2>
    <FormData
      level={3}
      name="frameworks.competency_ability"
      buttons={true}
      target={(competencyAbility, isNew) => isNew ?
        ['apiv2_competency_ability_create'] :
        ['apiv2_competency_ability_update', {id: competencyAbility.id}]
      }
      cancel={{
        type: LINK_BUTTON,
        target: props.competency && props.competency.parent ?
          `/frameworks/${props.competency.parent.id}/competency/${props.competency.id}` :
          props.competency ?
            `/frameworks/${props.competency.id}` :
            '/frameworks',
        exact: true
      }}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'ability.name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'level.id',
              type: 'choice',
              label: trans('level', {}, 'competency'),
              required: true,
              options: {
                condensed: true,
                choices: props.competency && props.competency.scale && props.competency.scale.levels ?
                  props.competency.scale.levels.reduce((acc, level) => Object.assign(acc, {[level.id]: level.value}), {}) :
                  {}
              }
            }, {
              name: 'ability.minResourceCount',
              type: 'number',
              label: trans('ability.min_resource_count', {}, 'competency'),
              required: true,
              options: {
                min: 0,
                max: 1000
              }
            }, {
              name: 'ability.minEvaluatedResourceCount',
              type: 'number',
              label: trans('ability.min_evaluated_resource_count', {}, 'competency'),
              required: true,
              options: {
                min: 0,
                max: 1000
              }
            }
          ]
        }
      ]}
    />
  </section>

CompetencyAbilityComponent.propTypes = {
  new: T.bool.isRequired,
  competencyAbility: T.shape(CompetencyAbilityType.propTypes),
  competency: T.shape(CompetencyType.propTypes)
}

const CompetencyAbility = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'frameworks.competency_ability')),
    competencyAbility: formSelect.data(formSelect.form(state, 'frameworks.competency_ability')),
    competency: formSelect.data(formSelect.form(state, 'frameworks.competency'))
  })
)(CompetencyAbilityComponent)

export {
  CompetencyAbility
}
