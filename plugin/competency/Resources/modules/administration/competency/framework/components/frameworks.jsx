import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {FrameworkList} from '#/plugin/competency/administration/competency/framework/components/framework-list'

const Frameworks = () =>
  <ListData
    name="frameworks.list"
    primaryAction={FrameworkList.open}
    fetch={{
      url: ['apiv2_competency_root_list'],
      autoload: true
    }}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `/frameworks/form/${rows[0].id}`
      }
    ]}
    delete={{
      url: ['apiv2_competency_delete_bulk']
    }}
    definition={FrameworkList.definition}
    card={FrameworkList.card}
  />

export {
  Frameworks
}
