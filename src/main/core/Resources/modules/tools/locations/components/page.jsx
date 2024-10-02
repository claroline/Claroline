import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'
import {PageHeading} from '#/main/app/page/components/heading'

const LocationPage = (props) => {
  if (isEmpty(props.location)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('location_loading', {}, 'location')}
      />
    )
  }

  return (
    <ToolPage
      poster={get(props.location, 'poster')}
      title={trans('location_name', {name: get(props.location, 'name', trans('loading'))}, 'location')}
      description={get(props.location, 'meta.description')}
    >
      {isEmpty(props.location) &&
        <ContentLoader
          size="lg"
          description={trans('location_loading', {}, 'location')}
        />
      }

      {!isEmpty(props.location) &&
        <PageHeading
          size="md"
          title={get(props.location, 'name', trans('loading'))}
          primaryAction="edit"
          actions={[
            {
              name: 'edit',
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              target: `${props.path}/${props.location.id}/edit`,
              primary: true
            }
          ]}
        />
      }

      {!isEmpty(props.location) && props.children}
    </ToolPage>
  )
}

LocationPage.propTypes = {
  path: T.string.isRequired,
  location: T.shape(
    LocationTypes.propTypes
  ),
  children: T.node
}

export {
  LocationPage
}
