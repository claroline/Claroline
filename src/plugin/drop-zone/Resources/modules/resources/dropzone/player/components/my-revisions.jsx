import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

const MyRevisionsComponent = props =>
  <Fragment>
    <h2>{trans('revisions_list', {}, 'dropzone')}</h2>

    <ListData
      name={`${selectors.STORE_NAME}.myRevisions`}
      fetch={{
        url: ['claro_dropzone_drop_revisions_list', {drop: props.myDropId}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `${props.path}/my/drop/revisions/${row.id}`,
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

MyRevisionsComponent.propTypes = {
  path: T.string.isRequired,
  myDropId: T.string.isRequired
}

const MyRevisions = connect(
  (state) => ({
    myDropId: selectors.myDropId(state)
  })
)(MyRevisionsComponent)

export {
  MyRevisions
}
