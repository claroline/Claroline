import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {useSelector} from 'react-redux'
import {selectors as configSelectors} from '#/main/app/config/store'
import {asset} from '#/main/app/config'

const PlatformBrand = props => {
  const brand = useSelector((state) => configSelectors.param(state, 'theme.logo'))
  const name = useSelector((state) => configSelectors.param(state, 'name'))

  if (brand) {
    return (
      <img
        className={classes('app-brand', props.className)}
        src={asset(brand)}
        alt={name}
      />
    )
  }

  return null
}

PlatformBrand.propTypes = {
  className: T.string
}

export {
  PlatformBrand
}
