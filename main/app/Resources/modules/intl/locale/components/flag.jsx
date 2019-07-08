import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'

const LocaleFlag = props =>
  <svg className={classes('locale-icon', props.className)}>
    <use xlinkHref={`${asset('bundles/clarolinecore/images/locale-icons.svg')}#icon-locale-${props.locale}`} />
  </svg>

LocaleFlag.propTypes = {
  className: T.string,
  locale: T.string.isRequired
}

export {
  LocaleFlag
}
