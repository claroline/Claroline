import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/path/resources/path/store'
import {Path as PathType} from '#/plugin/path/resources/path/prop-types'
import {MODAL_USER_PROGRESSION} from '#/plugin/path/resources/path/modals/user-progression'

const Progression = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('progression')}
      actions={[
        {
          name: 'download-connection-times',
          type: DOWNLOAD_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export-progression', {}, 'actions'),
          file: {
            url: ['innova_path_users_progression_csv', {id: props.path.id}]
          },
          group: trans('export')
        }
      ]}
    />

    <ListData
      name={selectors.STORE_NAME+'.analytics.evaluations'}
      fetch={{
        url: ['innova_path_progressions_fetch', {id: props.path.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: MODAL_BUTTON,
        modal: [MODAL_USER_PROGRESSION, {
          evaluation: row,
          path: props.path
        }]
      })}
      definition={[
        {
          name: 'userName',
          label: trans('user'),
          type: 'string',
          displayed: true
        }, {
          name: 'score',
          label: trans('progression'),
          type: 'number',
          displayed: true,
          filterable: false,
          sortable: false,
          calculated: (rowData) => `${rowData.progression}/${rowData.progressionMax}`
        }, {
          name: 'progression',
          label: trans('percentage'),
          type: 'number',
          displayed: true,
          filterable: false,
          calculated: (rowData) => `${rowData.progression && rowData.progressionMax ? Math.round((rowData.progression / rowData.progressionMax) * 100) : 0}%`
        }
      ]}
    />
  </Fragment>

Progression.propTypes = {
  path: T.shape(PathType.propTypes).isRequired
}

export {
  Progression
}
