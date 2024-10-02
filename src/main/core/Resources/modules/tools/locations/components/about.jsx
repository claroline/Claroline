import React, {} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PageSection} from '#/main/app/page'
import {ContentHtml} from '#/main/app/content/components/html'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'
import {selectors} from '#/main/core/tools/locations//store'

const LocationAbout = (props) =>
  <>
    {get(props.location, 'meta.description') &&
      <PageSection size="md">
        <ContentHtml className="lead mb-5 mt-4">{get(props.location, 'meta.description')}</ContentHtml>
      </PageSection>
    }

    <PageSection size="md" className="bg-body-tertiary">
      <DetailsData
        className="mt-3"
        name={`${selectors.STORE_NAME}.current`}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'phone',
                type: 'string',
                label: trans('phone')
              }, {
                name: 'address',
                type: 'address',
                label: trans('address')
              }
            ]
          }
        ]}
      />
    </PageSection>
  </>

LocationAbout.propTypes = {
  location: T.shape(
    LocationTypes.propTypes
  )
}

export {
  LocationAbout
}
