import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {ScaleList} from '#/plugin/competency/administration/competency/scale/components/scale-list'

const Scales = () =>
  <ListData
    name="scales.list"
    primaryAction={ScaleList.open}
    fetch={{
      url: ['apiv2_competency_scale_list'],
      autoload: true
    }}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `/scales/form/${rows[0].id}`
      }
    ]}
    delete={{
      url: ['apiv2_competency_scale_delete_bulk']
    }}
    definition={ScaleList.definition}
    card={ScaleList.card}
  />

export {
  Scales
}
