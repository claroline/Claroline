import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'
import {Competency as CompetencyType} from '#/plugin/competency/tools/evaluation/prop-types'
import {CompetencyAbilityCard} from '#/plugin/competency/tools/evaluation/data/components/competency-ability-card'

const CompetencyComponent = (props) =>
  <FormData
    level={2}
    title={props.new ? trans('competency.sub_creation', {}, 'competency') : trans('competency.sub_edition', {}, 'competency')}
    name={competencySelectors.STORE_NAME + '.frameworks.competency'}
    buttons={true}
    target={(competency, isNew) => isNew ?
      ['apiv2_competency_create'] :
      ['apiv2_competency_update', {id: competency.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.competency.parent ? `${props.path}/competencies/frameworks/${props.competency.parent.id}` : `${props.path}/competencies/frameworks/${props.competency.id}`,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }
        ]
      }
    ]}
  >
    {props.competency && (!props.competency.children || (props.competency.children && 0 === props.competency.children.length)) &&
      <FormSections level={3}>
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-atom"
          title={trans('abilities', {}, 'competency')}
          disabled={props.new}
          actions={[
            {
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('ability.create', {}, 'competency'),
              target: `${props.path}/competencies/frameworks/${props.competency.id}/ability`
            }, {
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-plus-square',
              label: trans('ability.add', {}, 'competency'),
              target: `${props.path}/competencies/frameworks/${props.competency.id}/ability_choice`
            }
          ]}
        >
          <ListData
            name={competencySelectors.STORE_NAME + '.frameworks.competency.abilities.list'}
            fetch={{
              url: ['apiv2_competency_ability_competency_list', {competency: props.competency.id}],
              autoload: props.competency.id && !props.new
            }}
            primaryAction={(row) => ({
              type: LINK_BUTTON,
              target: `${props.path}/competencies/frameworks/${row.competency.id}/ability/${row.id}`,
              label: trans('open', {}, 'actions')
            })}
            delete={{
              url: ['apiv2_competency_ability_delete_bulk']
            }}
            definition={[
              {
                name: 'ability.name',
                label: trans('name'),
                displayed: true,
                type: 'string',
                primary: true
              }, {
                name: 'level.name',
                label: trans('level', {}, 'competency'),
                displayed: true,
                type: 'string'
              }
            ]}
            card={CompetencyAbilityCard}
          />
        </FormSection>
      </FormSections>
    }
  </FormData>

CompetencyComponent.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  competency: T.shape(CompetencyType.propTypes)
}

const Competency = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, competencySelectors.STORE_NAME + '.frameworks.competency')),
    competency: formSelect.data(formSelect.form(state, competencySelectors.STORE_NAME + '.frameworks.competency'))
  })
)(CompetencyComponent)

export {
  Competency
}
