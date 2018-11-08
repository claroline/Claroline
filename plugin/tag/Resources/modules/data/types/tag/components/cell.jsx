import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataCell as DataCellTypes} from '#/main/app/data/prop-types'

const TagCell = props =>
  <div>
  </div>

implementPropTypes(TagCell, DataCellTypes, {
  data: T.shape({
    id: T.string,
    name: T.string,
    color: T.string
  })
})

export {
  TagCell
}
