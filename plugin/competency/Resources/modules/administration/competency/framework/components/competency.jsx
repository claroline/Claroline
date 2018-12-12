import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {Competency as CompetencyType} from '#/plugin/competency/administration/competency/prop-types'
import {CompetencyAbilityList} from '#/plugin/competency/administration/competency/framework/components/competency-ability-list'

const CompetencyComponent = (props) =>
  <section className="resource-section">
    <h2>{props.new ? trans('competency.sub_creation', {}, 'competency') : trans('competency.sub_edition', {}, 'competency')}</h2>
    <FormData
      level={3}
      name="frameworks.competency"
      buttons={true}
      target={(competency, isNew) => isNew ?
        ['apiv2_competency_create'] :
        ['apiv2_competency_update', {id: competency.id}]
      }
      cancel={{
        type: LINK_BUTTON,
        target: props.competency.parent ? `/frameworks/${props.competency.parent.id}` : `/frameworks/${props.competency.id}`,
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
            icon="fa fa-fw fa-graduation-cap"
            title={trans('abilities', {}, 'competency')}
            disabled={props.new}
            actions={[
              {
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('ability.create', {}, 'competency'),
                target: `/frameworks/${props.competency.id}/ability`
              }, {
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-plus-square',
                label: trans('ability.add', {}, 'competency'),
                target: `/frameworks/${props.competency.id}/ability_choice`
              }
            ]}
          >
            <ListData
              name="frameworks.competency.abilities.list"
              fetch={{
                url: ['apiv2_competency_ability_competency_list', {competency: props.competency.id}],
                autoload: props.competency.id && !props.new
              }}
              primaryAction={CompetencyAbilityList.open}
              delete={{
                url: ['apiv2_competency_ability_delete_bulk']
              }}
              definition={CompetencyAbilityList.definition}
              card={CompetencyAbilityList.card}
            />
          </FormSection>
        </FormSections>
      }
    </FormData>
  </section>

CompetencyComponent.propTypes = {
  new: T.bool.isRequired,
  competency: T.shape(CompetencyType.propTypes)
}

const Competency = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'frameworks.competency')),
    competency: formSelect.data(formSelect.form(state, 'frameworks.competency'))
  })
)(CompetencyComponent)

export {
  Competency
}
