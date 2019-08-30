import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ProfileFacet as ProfileFacetTypes} from '#/main/core/user/profile/prop-types'
import {getMainFacet} from '#/main/core/user/profile/utils'

const ProfileFacets = props => {
  const redirect = [{
    from: `${props.prefix}`,
    exact: true,
    to: `${props.prefix}/${getMainFacet(props.facets).id}`
  }]
  
  return (
    <Routes
      routes={[
        {
          path: `${props.prefix}/:id`,
          onEnter: (params) => props.openFacet(params.id),
          component: props.facetComponent
        }
      ]}

      redirect={redirect}
    />
  )
}

ProfileFacets.propTypes = {
  prefix: T.string,
  facets: T.arrayOf(T.shape(
    ProfileFacetTypes.propTypes
  )).isRequired,
  openFacet: T.func.isRequired,
  facetComponent: T.any.isRequired // todo find better typing
}

ProfileFacets.defaultProps = {
  prefix: ''
}

export {
  ProfileFacets
}
