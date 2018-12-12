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

const CompetencyAbilityChoiceComponent = (props) =>
  <section className="resource-section">
    <h2>{trans('ability.addition', {}, 'competency')}</h2>
    <FormData
      level={3}
      name="frameworks.competency_ability"
      buttons={true}
      target={['apiv2_competency_ability_create']}
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
              name: 'ability',
              type: 'ability',
              label: trans('ability', {}, 'competency'),
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
            }
          ]
        }
      ]}
    />
  </section>

CompetencyAbilityChoiceComponent.propTypes = {
  new: T.bool.isRequired,
  competencyAbility: T.shape(CompetencyAbilityType.propTypes),
  competency: T.shape(CompetencyType.propTypes)
}

const CompetencyAbilityChoice = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'frameworks.competency_ability')),
    competencyAbility: formSelect.data(formSelect.form(state, 'frameworks.competency_ability')),
    competency: formSelect.data(formSelect.form(state, 'frameworks.competency'))
  })
)(CompetencyAbilityChoiceComponent)

export {
  CompetencyAbilityChoice
}
