import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool'

import {Material as MaterialTypes} from '#/main/core/tools/locations/prop-types'
import {MODAL_MATERIAL_BOOKING} from '#/main/core/tools/locations/material/modals/booking'
import {MODAL_MATERIAL_PARAMETERS} from '#/main/core/tools/locations/material/modals/parameters'

const MaterialPage = (props) => {
  if (isEmpty(props.material)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('material_loading', {}, 'location')}
      />
    )
  }

  return (
    <ToolPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans('materials', {}, 'location'),
          target: `${props.path}/materials`
        }, {
          type: LINK_BUTTON,
          label: get(props.material, 'name'),
          target: `${props.path}/materials/${get(props.material, 'id')}`,
          displayed: !!props.material
        }
      ]}
      poster={get(props.material, 'poster')}
      title={get(props.material, 'name') || trans('locations', {}, 'tools')}
      subtitle={get(props.material, 'code') || trans('materials', {}, 'location')}
      primaryAction="book"
      actions={[
        {
          name: 'book',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-calendar-plus',
          label: trans('book', {}, 'actions'),
          modal: [MODAL_MATERIAL_BOOKING, {
            material: props.material,
            onSave: () => props.invalidateBookings()
          }],
          primary: true
        }, {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_MATERIAL_PARAMETERS, {
            material: props.material,
            onSave: () => true // TODO : reload
          }],
          displayed: props.editable,
          group: trans('management')
        }
      ]}
    >
      {props.children}
    </ToolPage>
  )
}

MaterialPage.propTypes = {
  path: T.string.isRequired,
  material: T.shape(
    MaterialTypes.propTypes
  ),
  editable: T.bool.isRequired,
  bookable: T.bool.isRequired,
  invalidateBookings: T.func.isRequired,
  children: T.node
}

export {
  MaterialPage
}
