import React  from 'react'

import {asset} from '#/main/core/asset'

const T = React.PropTypes

const Icon = props =>
  <svg className={`item-icon item-icon-${props.size}`}>
    <use xlinkHref={`${asset('bundles/ujmexo/images/item-icons.svg')}#icon-${props.name}`} />
  </svg>

Icon.propTypes = {
  name: T.string.isRequired,
  size: T.oneOf(['sm', 'lg'])
}

Icon.defaultProps = {
  size: 'sm'
}

export {Icon}
