import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {select} from '#/plugin/drop-zone/resources/dropzone/store/selectors'

const MyRevisionsComponent = props =>
  <section className="resource-section revisions-list">
    <h2>{trans('revisions_list', {}, 'dropzone')}</h2>

    <ListData
      name={`${select.STORE_NAME}.myRevisions`}
      fetch={{
        url: ['claro_dropzone_drop_revisions_list', {drop: props.myDropId}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: `/my/drop/revisions/${row.id}`,
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
  </section>

MyRevisionsComponent.propTypes = {
  myDropId: T.string.isRequired
}

const MyRevisions = connect(
  (state) => ({
    myDropId: select.myDropId(state)
  })
)(MyRevisionsComponent)

export {
  MyRevisions
}
