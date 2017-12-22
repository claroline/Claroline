import {DataCell as DataCellTypes} from '#/main/core/data/prop-types'

import {getPlainText} from '#/main/core/data/types/html/utils'

const HtmlCell = props => getPlainText(props.data)

HtmlCell.propTypes = DataCellTypes.propTypes

export {
  HtmlCell
}
