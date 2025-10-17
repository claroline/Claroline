import React from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors, ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'
import {ListData} from '#/main/app/content/list/containers/data'

import {actions, selectors} from '#/main/core/tools/locations/store'
import {LocationCard} from '#/main/core/data/types/location/components/card'
import {MODAL_LOCATION_FORM} from '#/main/core/tools/locations/modals/form'

const LocationList = () => {
  const dispatch = useDispatch()

  const path = useSelector(toolSelectors.path)

  return (
    <ToolPage
      title={trans('locations')}
    >
      <PageListSection
        title={trans('locations')}
        addAction={{
          name: 'add',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_location', {}, 'actions'),
          modal: [MODAL_LOCATION_FORM, {
            isNew: true,
            onSave: (updatedLocation) => {
              dispatch(actions.loadLocation(updatedLocation))
              dispatch(actions.invalidateLocations())
            }
          }]
        }}
      >
        <ListData
          className="mb-5"
          name={`${selectors.STORE_NAME}.list`}
          flush={true}
          fetch={{
            url: ['apiv2_location_list'],
            autoload: true
          }}
          definition={[
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: true,
              primary: true
            }, {
              name: 'meta.description',
              type: 'string',
              label: trans('description'),
              options: {long: true},
              sortable: false
            }, {
              name: 'address',
              type: 'address',
              label: trans('address'),
              displayed: true
            }, {
              name: 'phone',
              type: 'string',
              label: trans('phone'),
              displayed: true
            }
          ]}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${path}/${row.id}`,
            label: trans('open', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_location_delete']
          }}
          actions={(rows) => [
            {
              name: 'edit',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              modal: [MODAL_LOCATION_FORM, {
                isNew: false,
                location: rows[0],
                onSave: (updatedLocation) => {
                  dispatch(actions.loadLocation(updatedLocation))
                  dispatch(actions.invalidateLocations())
                }
              }],
              group: trans('management'),
              scope: ['object']
            }
          ]}
          card={LocationCard}
        />
      </PageListSection>
    </ToolPage>
  )
}

export {
  LocationList
}
