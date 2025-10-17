import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'
import {PageContent, PageHeading, PageSection, PageToolbar, PageHeadingSkeleton, PageToolbarSkeleton} from '#/main/app/page'
import {TextSkeleton} from '#/main/app/components/placeholder'

import {actions} from '#/main/core/tools/locations/store'
import {MODAL_LOCATION_FORM} from '#/main/core/tools/locations/modals/form'
import {Location as LocationTypes} from '#/main/core/data/types/location/prop-types'

const LocationPage = (props) => {
  const dispatch = useDispatch()

  return (
    <ToolPage
      title={trans('location_name', {name: get(props.location, 'name', trans('loading'))}, 'location')}
      description={get(props.location, 'meta.description')}
    >
      {isEmpty(props.location) &&
        <PageContent className="placeholder-glow">
          <PageToolbarSkeleton toolbar="edit more" />
          <PageHeadingSkeleton />
          <PageSection className="mb-5">
            <TextSkeleton />
          </PageSection>
        </PageContent>
      }

      {!isEmpty(props.location) &&
        <PageContent poster={get(props.location, 'poster')}>
          <PageToolbar
            toolbar="edit more"
            actions={[
              {
                name: 'edit',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit', {}, 'actions'),
                modal: [MODAL_LOCATION_FORM, {
                  isNew: false,
                  location: props.location,
                  onSave: (updatedLocation) => {
                    dispatch(actions.loadLocation(updatedLocation))
                    dispatch(actions.invalidateLocations())
                  }
                }],
                primary: true
              }
            ]}
          />

          <PageHeading
            title={get(props.location, 'name')}
          />

          {props.children}
        </PageContent>
      }
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
