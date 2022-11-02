import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {ProfileFacet as ProfileFacetTypes} from '#/main/community/profile/prop-types'
import {getMainFacet} from '#/main/community/profile/utils'

const ProfileFacets = props =>
  <Routes
    routes={[
      {
        path: `${props.prefix}/:id`,
        onEnter: (params) => props.openFacet(params.id),
        render: () => {
          const Facet = createElement(props.facetComponent, {
            path: props.prefix
          })

          return Facet
        }
      }
    ]}

    redirect={[{
      from: `${props.prefix}`,
      exact: true,
      to: `${props.prefix}/${getMainFacet(props.facets).id}`
    }]}
  />

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
