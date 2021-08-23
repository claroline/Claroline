import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {DropzoneType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

const RevisionsComponent = props =>
  <Fragment>
    <h2>{trans('revisions_list', {}, 'dropzone')}</h2>

    <ListData
      name={`${selectors.STORE_NAME}.revisions`}
      fetch={{
        url: ['claro_dropzone_revisions_list', {id: props.dropzone.id}],
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
  </Fragment>

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
