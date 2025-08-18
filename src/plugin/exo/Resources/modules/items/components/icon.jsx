import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'

const ItemIcon = ({className, name, size}) =>
  <svg className={classes(className, `item-icon item-icon-${size} flex-shrink-0`)}>
    <use xlinkHref={`${asset('bundles/ujmexo/images/item-icons.svg')}#icon-quiz-${name}`} />
  </svg>

ItemIcon.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  size: T.oneOf(['xs', 'sm', 'md', 'lg']).isRequired
}

export {
  ItemIcon
}
