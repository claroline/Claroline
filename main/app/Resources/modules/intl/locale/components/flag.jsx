import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/scaffolding/asset'

const LocaleFlag = props =>
  <svg className="locale-icon">
    <use xlinkHref={`${asset('bundles/clarolinecore/images/locale-icons.svg')}#icon-locale-${props.locale}`} />
  </svg>

LocaleFlag.propTypes = {
  locale: T.string.isRequired
}

export {
  LocaleFlag
}
