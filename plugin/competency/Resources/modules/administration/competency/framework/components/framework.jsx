import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {TreeData} from '#/main/app/content/tree/containers/data'

import {FrameworkList} from '#/plugin/competency/administration/competency/framework/components/framework-list'

const Framework = () =>
  <TreeData
    name="frameworks.current"
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: row.parent ?
        `/frameworks/${row.parent.id}/competency/${row.id}` :
        `/frameworks/${row.id}/competency/${row.id}`,
      label: trans('open', {}, 'actions')
    })}
    delete={{
      url: ['apiv2_competency_delete_bulk']
    }}
    definition={FrameworkList.definition}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: rows[0].parent ?
          `/frameworks/${rows[0].parent.id}/competency/${rows[0].id}` :
          `/frameworks/${rows[0].id}/competency/${rows[0].id}`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('competency.create_sub', {}, 'competency'),
        displayed: 0 === rows[0].abilities.length,
        scope: ['object'],
        target: `/frameworks/${rows[0].id}/competency`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('ability.create', {}, 'competency'),
        displayed: 0 === rows[0].children.length,
        scope: ['object'],
        target: `/frameworks/${rows[0].id}/ability`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus-square',
        label: trans('ability.add', {}, 'competency'),
        displayed: 0 === rows[0].children.length,
        scope: ['object'],
        target: `/frameworks/${rows[0].id}/ability_choice`
      }
    ]}
    card={(row) => ({
      icon: 'fa fa-graduation-cap',
      title: row.name,
      subtitle: row.abilities.map(competencyAbility => competencyAbility.ability.name).join(', '),
      flags: [
        row.abilities && 0 < row.abilities.length && ['fa fa-graduation-cap', trans('ability.contains_desc', {}, 'competency')]
      ].filter(flag => !!flag)
    })}
  />

export {
  Framework
}
