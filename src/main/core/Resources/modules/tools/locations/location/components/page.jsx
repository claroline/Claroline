import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {PageFull} from '#/main/app/page/components/full'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'
import {getAddressString} from '#/main/app/data/types/address/utils'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'

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
    <PageFull
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb('locations', props.currentContext.type, props.currentContext.data), [
        {
          type: LINK_BUTTON,
          label: trans('locations', {}, 'tools'),
          target: `${props.path}/locations`
        }, {
          type: LINK_BUTTON,
          label: get(props.location, 'name'),
          target: `${props.path}/locations/${get(props.location, 'id')}`
        }
      ])}
      poster={get(props.location, 'poster.url')}
      title={get(props.location, 'name')}
      subtitle={getAddressString(get(props.location, 'address'))}
      toolbar="edit | fullscreen more"
      actions={[
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: `${props.path}/locations/${props.location.id}/edit`,
          primary: true
        }
      ]}
    >
      {props.children}
    </PageFull>
  )
}

LocationPage.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.string.isRequired,
    data: T.object
  }).isRequired,
  location: T.shape(
    LocationTypes.propTypes
  ),
  children: T.node
}

export {
  LocationPage
}
