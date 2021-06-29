import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Routes} from '#/main/app/router/components/routes'
import {ContentTabs} from '#/main/app/content/components/tabs'
import {Sections, Section} from '#/main/app/content/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {Material as MaterialTypes} from '#/main/core/tools/locations/prop-types'
import {MaterialPage} from '#/main/core/tools/locations/material/containers/page'
import {selectors} from '#/main/core/tools/locations/material/store/selectors'
import {MODAL_MATERIAL_BOOKING} from '#/main/core/tools/locations/material/modals/booking'

const MaterialAbout = (props) =>
  <DetailsData
    name={selectors.FORM_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'description',
            type: 'html',
            label: trans('description'),
            required: true
          }, {
            name: 'quantity',
            type: 'number',
            label: trans('quantity'),
            required: true,
            options: {
              min: 0
            }
          }, {
            name: 'location',
            type: 'location',
            label: trans('location')
          },
        ]
      }
    ]}
  >
    <Sections
      level={3}
    >
      <Section
        className="embedded-list-section"
        icon="fa fa-fw fa-calendar"
        title={trans('bookings', {}, 'location')}
        actions={[
          {
            name: 'book',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('book', {}, 'actions'),
            modal: [MODAL_MATERIAL_BOOKING, {
              material: props.material,
              onSave: () => props.invalidateBookings()
            }]
          }
        ]}
      >
        <ListData
          name={`${selectors.FORM_NAME}.bookings`}
          fetch={{
            url: ['apiv2_booking_material_list_booking', {material: props.material.id}],
            autoload: true
          }}
          delete={{
            url: ['apiv2_booking_material_delete_booking', {material: props.material.id}]
          }}
          definition={[
            {
              name: 'dates[0]',
              alias: 'startDate',
              type: 'date',
              label: trans('start_date'),
              options: {time: true},
              displayed: true
            }, {
              name: 'dates[1]',
              alias: 'endDate',
              type: 'date',
              label: trans('end_date'),
              options: {time: true},
              displayed: true
            }, {
              name: 'description',
              type: 'string',
              label: trans('description'),
              options: {long: true},
              displayed: true
            }
          ]}
        />
      </Section>
    </Sections>
  </DetailsData>

MaterialAbout.propTypes = {
  material: T.shape(
    MaterialTypes.propTypes
  ),
  invalidateBookings: T.func.isRequired
}

const MaterialDetails = (props) =>
  <MaterialPage
    material={props.material}
  >
    <header className="row content-heading">
      <ContentTabs
        backAction={{
          type: LINK_BUTTON,
          target: `${props.path}/materials`,
          exact: true
        }}
        sections={[
          {
            name: 'about',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-info',
            label: trans('about'),
            target: `${props.path}/materials/${props.material.id}`,
            exact: true
          }, {
            name: 'planning',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-calendar',
            label: trans('planning', {}, 'location'),
            target: `${props.path}/materials/${props.material.id}/planning`,
            displayed: false
          }
        ]}
      />
    </header>

    <Routes
      path={`${props.path}/materials/${props.material.id}`}
      routes={[
        {
          path: '/',
          exact: true,
          render: () => (
            <MaterialAbout
              material={props.material}
              invalidateBookings={props.invalidateBookings}
            />
          )
        }
      ]}
    />
  </MaterialPage>

MaterialDetails.propTypes = {
  path: T.string.isRequired,
  material: T.shape(
    MaterialTypes.propTypes
  ),
  invalidateBookings: T.func.isRequired
}

export {
  MaterialDetails
}
