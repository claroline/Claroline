import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {DetailsData} from '#/main/app/content/details'
import {PageSection} from '#/main/app/page'

import {LocationPage} from '#/main/core/tools/locations//containers/page'
import {selectors} from '#/main/core/tools/locations/store'

const LocationShow = () => {
  const location = useSelector(selectors.currentLocation)

  return (
    <LocationPage
      location={location}
    >
      <PageSection className="mb-5">
        {get(location, 'meta.description') &&
          <Html className="content-text mb-5">{get(location, 'meta.description')}</Html>
        }

        <DetailsData
          data={location}
          definition={[
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
    </LocationPage>
  )
}

export {
  LocationShow
}
