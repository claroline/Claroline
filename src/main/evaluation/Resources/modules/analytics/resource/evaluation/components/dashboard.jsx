import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ListData} from '#/main/app/content/list/containers/data'

import resourceEvaluationSource from '#/main/evaluation/data/sources/resource-evaluations'
import {MODAL_RESOURCE_EVALUATIONS} from '#/main/evaluation/modals/resource-evaluations'
import {selectors} from '#/main/evaluation/analytics/resource/evaluation/store'

const EvaluationDashboard = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('evaluation', {}, 'tools')}
      actions={[
        {
          name: 'export-csv',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-csv', {}, 'actions'),
          file: {
            url: ['apiv2_resource_evaluation_csv', {nodeId: props.nodeId}]
          },
          group: trans('export')
        }
      ]}
    />

    <ListData
      name={selectors.STORE_NAME}
      fetch={{
        url: ['apiv2_resource_evaluation_list', {nodeId: props.nodeId}],
        autoload: true
      }}
      definition={resourceEvaluationSource.parameters.definition.filter(prop => 'resourceNode' !== prop.name)}
      actions={(rows) => [{
        name: 'about',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-info',
        label: trans('show-info', {}, 'actions'),
        modal: [MODAL_RESOURCE_EVALUATIONS, {
          userEvaluation: rows[0]
        }],
        scope: ['object']
      }]}
    />
  </Fragment>

EvaluationDashboard.propTypes = {
  nodeId: T.string.isRequired
}

export {
  EvaluationDashboard
}
