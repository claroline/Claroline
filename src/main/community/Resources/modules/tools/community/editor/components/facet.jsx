import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormContent} from '#/main/app/content/form/containers/content'
import {FormParameters} from '#/main/app/content/form/parameters/containers/main'

import {selectors} from '#/main/core/tool/editor/store'
import {ProfileFacet as ProfileFacetTypes} from '#/main/community/profile/prop-types'

const EditorFacet = props =>
  <>
    <FormContent
      level={2}
      name={selectors.STORE_NAME}
      dataPart={`profile[${props.index}]`}
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
              displayed: (facet) => !get(facet, 'meta.main', false)
            }
          ]
        }
      ]}
    />

    <FormParameters
      name={selectors.STORE_NAME}
      dataPart={`profile[${props.index}].sections`}
      sections={props.facet.sections}
    />
  </>

EditorFacet.propTypes = {
  index: T.number.isRequired,
  facet: T.shape(
    ProfileFacetTypes.propTypes
  ).isRequired
}

EditorFacet.defaultProps = {
  facet: ProfileFacetTypes.defaultProps
}

export {
  EditorFacet
}
