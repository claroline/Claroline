import isEmpty from 'lodash/isEmpty'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

const EventCell = props => {
  if (!isEmpty(props.data)) {
    return props.data.name
  }

  return '-'
}

implementPropTypes(EventCell, DataCellTypes, {
  data: T.shape({
    id: T.string,
    name: T.string
  })
})

export {
  EventCell
}
