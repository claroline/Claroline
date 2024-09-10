import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {ResourcePage} from '#/main/core/resource'

const RevisionsComponent = props =>
  <ResourcePage title={trans('revisions_list', {}, 'dropzone')}>
    <ListData
      name={`${selectors.STORE_NAME}.revisions`}
      fetch={{
        url: ['apiv2_droprevision_dropzone_list', {id: props.dropzone.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/revisions/${row.id}`,
        label: trans('show_revision', {}, 'dropzone')
      })}
      definition={[
        {
          name: 'creator',
          label: trans('creator'),
          type: 'string',
          displayed: true,
          render: (rowData) => rowData.creator ?
            `${rowData.creator.firstName} ${rowData.creator.lastName}` :
            trans('unknown')
        }, {
          name: 'creationDate',
          label: trans('creation_date'),
          type: 'date',
          displayed: true,
          filterable: false,
          options: {
            time: true
          }
        }
      ]}
    />
  </ResourcePage>

RevisionsComponent.propTypes = {
  path: T.string.isRequired,
  dropzone: T.shape(DropzoneType.propTypes).isRequired
}

const Revisions = connect(
  (state) => ({
    dropzone: selectors.dropzone(state)
  })
)(RevisionsComponent)

export {
  Revisions
}
