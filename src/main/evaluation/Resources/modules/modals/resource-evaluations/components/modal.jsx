import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/core/resource/constants'
import {ResourceUserEvaluation as ResourceUserEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {selectors} from '#/main/evaluation/modals/resource-evaluations/store'

const ResourceEvaluationsModal = props =>
  <Modal
    {...omit(props, 'userEvaluation', 'reset')}
    icon="fa fa-fw fa-folder"
    title={props.userEvaluation.resourceNode.name}
    className="data-picker-modal"
    bsSize="lg"
    onEnter={props.reset}
  >
    <ListData
      name={selectors.STORE_NAME}
      fetch={{
        url: ['apiv2_resource_evaluation_list_attempts', {userEvaluationId: props.userEvaluation.id}],
        autoload: true
      }}
      definition={[
        {
          name: 'date',
          type: 'date',
          label: trans('last_activity'),
          options: {
            time: true
          },
          displayed: true
        }, {
          name: 'status',
          type: 'choice',
          label: trans('status'),
          options: {
            choices: constants.EVALUATION_STATUSES
          },
          displayed: true
        }, {
          name: 'progression',
          type: 'progression',
          label: trans('progression'),
          displayed: true,
          filterable: false,
          calculated: (row) => ((row.progression || 0) / (row.progressionMax || 1)) * 100,
          options: {
            type: 'user'
          }
        }, {
          name: 'score',
          type: 'score',
          label: trans('score'),
          calculated: (row) => {
            if (row.scoreMax) {
              return {
                current: row.score,
                total: row.scoreMax
              }
            }

            return null
          },
          displayed: true,
          filterable: false
        }
      ]}
      selectable={false}
    />
  </Modal>

ResourceEvaluationsModal.propTypes = {
  userEvaluation: T.shape(
    ResourceUserEvaluationTypes.propTypes
  ).isRequired,
  fadeModal: T.func.isRequired,

  // from store
  reset: T.func.isRequired
}

export {
  ResourceEvaluationsModal
}
