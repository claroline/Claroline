import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'
import {ScaleCard} from '#/plugin/competency/tools/evaluation/data/components/scale-card'

const Scales = (props) =>
  <ListData
    name={competencySelectors.STORE_NAME + '.scales.list'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/competencies/scales/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
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
        target: `${props.path}/competencies/scales/form/${rows[0].id}`
      }
    ]}
    delete={{
      url: ['apiv2_competency_scale_delete_bulk']
    }}
    definition={[
      {
        name: 'name',
        label: trans('name'),
        displayed: true,
        type: 'string',
        primary: true
      }
    ]}
    card={ScaleCard}
  />

Scales.propTypes = {
  path: T.string.isRequired
}

export {
  Scales
}
