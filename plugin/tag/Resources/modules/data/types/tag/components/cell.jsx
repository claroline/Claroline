import React, {Fragment} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

const TagCell = (props) => {
  if (0 !== props.data.length) {
    return (
      <Fragment>
        {props.data.map(tag =>
          <span key={toKey(tag)} className="label label-info">{tag}</span>
        )}
      </Fragment>
    )
  }

  return '-'
}

implementPropTypes(TagCell, DataCellTypes, {
  data: T.arrayOf(T.string)
}, {
  data: []
})

export {
  TagCell
}
