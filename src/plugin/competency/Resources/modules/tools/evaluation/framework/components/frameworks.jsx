import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as competencySelectors} from '#/plugin/competency/tools/evaluation/store'
import {CompetencyCard} from '#/plugin/competency/tools/evaluation/data/components/competency-card'

const Frameworks = (props) =>
  <ListData
    name={competencySelectors.STORE_NAME + '.frameworks.list'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/competencies/frameworks/${row.id}`,
      label: trans('open', {}, 'actions')
    })}
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
        target: `${props.path}/competencies/frameworks/form/${rows[0].id}`
      }, {
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export'),
        scope: ['object'],
        file: {
          url: url(['apiv2_competency_framework_export', {id: rows[0].id}])
        }
      }
    ]}
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
    card={CompetencyCard}
  />

Frameworks.propTypes = {
  path: T.string.isRequired
}

export {
  Frameworks
}
