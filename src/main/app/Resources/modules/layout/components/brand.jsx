import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config'

const AppBrand = props => {
  if (props.logo) {
    return (
      <img
        className="app-brand"
        src={asset(props.logo)}
        alt={props.name}
      />
    )
  }

  return null
}

AppBrand.propTypes = {
  logo: T.string,
  name: T.string.isRequired
}

export {
  AppBrand
}
