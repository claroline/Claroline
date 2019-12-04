import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'

const TemplateTypeCell = props => {
  if (!isEmpty(props.data)) {
    return trans(props.data.name, {}, 'template')
  }

  return '-'
}

TemplateTypeCell.propTypes = DataCellTypes.propTypes

TemplateTypeCell.defaultProps = {
  data: {}
}

export {
  TemplateTypeCell
}
