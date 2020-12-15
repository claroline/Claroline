import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {constants as listConst} from '#/main/app/content/list/constants'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors} from '#/main/theme/administration/appearance/icon/store/selectors'
import {IconSet as IconSetType} from '#/main/theme/administration/appearance/icon/prop-types'
import {IconItemCard} from '#/main/theme/administration/appearance/icon/components/icon-item-card'

const Icon = (props) =>
  <FormData
    level={2}
    title={props.new ? trans('icon_set_creation') : trans('icon_set_edition')}
    name={selectors.STORE_NAME+'.current'}
    target={(iconSet, isNew) => isNew ?
      ['apiv2_icon_set_create'] :
      ['apiv2_icon_set_update', {id: iconSet.id}]
    }
    buttons={true}
    disabled={!props.iconSet.editable}
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/appearance/icons',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }
        ]
      }
    ]}
  >
    <FormSections
      level={3}
    >
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-icons"
        title={trans('icons')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_icon_item'),
            callback: () => props.openIconItemForm(props.iconSet, props.mimeTypes, {id: makeId()}),
            displayed: !props.new && props.iconSet.editable
          }
        ]}
      >
        <ListData
          name={`${selectors.STORE_NAME}.items`}
          fetch={{
            url: ['apiv2_icon_set_items_list', {iconSet: props.iconSet.id}],
            autoload: props.iconSet.id && !props.new
          }}
          display={{
            current: listConst.DISPLAY_TILES_SM
          }}
          primaryAction={(row) => ({
            type: CALLBACK_BUTTON,
            label: trans('edit', {}, 'actions'),
            callback: () => props.openIconItemForm(props.iconSet, props.mimeTypes, {}, row.id),
            displayed: props.iconSet.editable
          })}
          delete={{
            url: ['apiv2_icon_item_delete_bulk'],
            displayed: () => props.iconSet.editable
          }}
          definition={[
            {
              name: 'mimeType',
              type: 'string',
              label: trans('mime_type'),
              displayed: true,
              primary: true
            }
          ]}
          card={IconItemCard}
        />
      </FormSection>
    </FormSections>
  </FormData>

Icon.propTypes = {
  path: T.string,
  new: T.bool,
  iconSet: T.shape(IconSetType.propTypes),
  mimeTypes: T.arrayOf(T.string),
  openIconItemForm: T.func
}

export {
  Icon
}
