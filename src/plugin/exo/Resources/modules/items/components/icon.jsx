import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'

const Icon = props =>
  <svg className={classes(props.className, `item-icon item-icon-${props.size}`)}>
    <use xlinkHref={`${asset('bundles/ujmexo/images/item-icons.svg')}#icon-quiz-${props.name}`} />
  </svg>

Icon.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  size: T.oneOf(['sm', 'md', 'lg'])
}

Icon.defaultProps = {
  size: 'sm'
}

export {
  Icon,
  Icon as ItemIcon
}
