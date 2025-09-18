import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {DescriptionList} from '#/main/app/data/components/description-list'

const ContentInfo = (props) => {
  return (
    <div className="rounded-3 bg-body-tertiary p-4">
      <h6 className="page-section-title mb-4">Informations sur la séquence</h6>
      <DescriptionList
        className="mb-0"
        bordered={true}
        fields={[
          {
            name: 'creator',
            label: trans('created_by'),
            type: 'user'
          }, {
            name: 'createdAt',
            label: trans('created_at'),
            type: 'date',
            options: {time: true}
          }, {
            name: 'updatedAt',
            label: trans('updated_at'),
            type: 'date',
            options: {time: true}
          }
        ]}
        data={{
          creator: props.creator,
          createdAt: props.createdAt,
          updatedAt: props.updatedAt
        }}
      />
    </div>
  )
}

ContentInfo.propTypes = {
  creator: T.object,
  createdAt: T.string,
  updatedAt: T.string
}

export {
  ContentInfo
}
