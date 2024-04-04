import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config'

const AppBrand = props => {
  if (props.logo) {
    return (
      <img
        className={classes('app-brand', props.className)}
        src={asset(props.logo)}
        alt={props.name}
      />
    )
  }

  return null
}

AppBrand.propTypes = {
  className: T.string,
  logo: T.string,
  name: T.string.isRequired
}

export {
  AppBrand
}
