import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormParameters} from '#/main/app/content/form/parameters/containers/main'

import {ProfileFacet as ProfileFacetTypes} from '#/main/community/profile/prop-types'
import {selectors} from '#/main/community/tools/community/profile/store'

const ProfileFacet = props =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    dataPart={`[${props.index}]`}
    buttons={true}
    target={['apiv2_profile_configure']}
    definition={[
      {
        icon: 'fa fa-fw fa-cog',
        title: trans('parameters'),
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          }, {
            name: 'display.creation',
            type: 'boolean',
            label: trans('display_on_create'),
            displayed: !props.facet.meta.main
          }
        ]
      }
    ]}
  >
    <FormParameters
      name={selectors.FORM_NAME}
      dataPart={`[${props.index}].sections`}
      sections={props.facet.sections}
    />
  </FormData>

ProfileFacet.propTypes = {
  index: T.number.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired,
  fields: T.array
}

ProfileFacet.defaultProps = {
  facet: ProfileFacetTypes.defaultProps
}

export {
  ProfileFacet
}
