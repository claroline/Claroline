import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/path/resources/path/store'
import {Path as PathType} from '#/plugin/path/resources/path/prop-types'
import {MODAL_USER_PROGRESSION} from '#/plugin/path/resources/path/modals/user-progression'
import {constants} from '#/main/evaluation/resource/constants'

const Progression = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('progression')}
    />

    <ListData
      name={selectors.STORE_NAME+'.analytics.evaluations'}
      fetch={{
        url: ['apiv2_resource_evaluation_list', {nodeId: props.nodeId}],
        autoload: true
      }}
      definition={[
        {
          name: 'user',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
          name: 'date',
          type: 'date',
          label: trans('date'),
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
          name: 'duration',
          type: 'time',
          label: trans('duration'),
          displayed: true,
          filterable: false
        }, {
          name: 'progression',
          label: trans('progression'),
          type: 'progression',
          displayed: true,
          filterable: false,
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
                current: get(props.path, 'score.total') ? (row.score / row.scoreMax) * get(props.path, 'score.total') : row.score,
                total: get(props.path, 'score.total') || row.scoreMax
              }
            }

            return null
          },
          displayed: !!get(props.path, 'score.total'),
          displayable: !!get(props.path, 'score.total'),
          filterable: false
        }
      ]}
      actions={(rows) => [{
        name: 'open',
        icon: 'fa fa-fw fa-eye',
        label: trans('open', {}, 'actions'),
        type: MODAL_BUTTON,
        modal: [MODAL_USER_PROGRESSION, {
          evaluation: rows[0],
          path: props.path
        }],
        scope: ['object']
      }]}
      selectable={false}
    />
  </Fragment>

Progression.propTypes = {
  nodeId: T.string.isRequired,
  path: T.shape(PathType.propTypes).isRequired
}

export {
  Progression
}
