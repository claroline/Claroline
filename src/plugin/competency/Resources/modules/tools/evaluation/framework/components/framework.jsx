import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {TreeData} from '#/main/app/content/tree/containers/data'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'
import {CompetencyTreeCard} from '#/plugin/competency/tools/evaluation/data/components/competency-card'

const Framework = (props) =>
  <TreeData
    name={competencySelectors.STORE_NAME + '.frameworks.current'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: row.parent ?
        `${props.path}/competencies/frameworks/${row.parent.id}/competency/${row.id}` :
        `${props.path}/competencies/frameworks/${row.id}/competency/${row.id}`,
      label: trans('open', {}, 'actions')
    })}
    delete={{
      url: ['apiv2_competency_delete_bulk']
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        type: 'string',
        primary: true
      }, {
        name: 'description',
        label: trans('description'),
        displayed: true,
        type: 'html'
      }, {
        name: 'scale',
        alias: 'scale.name',
        label: trans('scale', {}, 'competency'),
        displayed: true,
        type: 'string',
        calculated: (rowData) => rowData.scale.name
      }
    ]}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: rows[0].parent ?
          `${props.path}/competencies/frameworks/${rows[0].parent.id}/competency/${rows[0].id}` :
          `${props.path}/competencies/frameworks/${rows[0].id}/competency/${rows[0].id}`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('competency.create_sub', {}, 'competency'),
        displayed: 0 === rows[0].abilities.length,
        scope: ['object'],
        target: `${props.path}/competencies/frameworks/${rows[0].id}/competency`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('ability.create', {}, 'competency'),
        displayed: 0 === rows[0].children.length,
        scope: ['object'],
        target: `${props.path}/competencies/frameworks/${rows[0].id}/ability`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus-square',
        label: trans('ability.add', {}, 'competency'),
        displayed: 0 === rows[0].children.length,
        scope: ['object'],
        target: `${props.path}/competencies/frameworks/${rows[0].id}/ability_choice`
      }
    ]}
    card={CompetencyTreeCard}
  />

Framework.propTypes = {
  path: T.string.isRequired
}

export {
  Framework
}
