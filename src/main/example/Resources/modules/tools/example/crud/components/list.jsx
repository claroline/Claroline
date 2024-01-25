import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ListData} from '#/main/app/content/list/containers/data'

import {CrudCard} from '#/main/example/tools/example/crud/components/card'
import {selectors} from '#/main/example/tools/example/crud/store/selectors'

const CrudList = (props) =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: 'Simple CRUD',
      target: `${props.path}/crud`
    }]}
    subtitle="Simple CRUD"
    /*primaryAction="add"*/
    primaryAction={
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: 'Add',
        target: `${props.path}/crud/new`,
        primary: true
      }
    }
  >
    <ContentSizing size="full">
      <ListData
        flush={true}
        name={selectors.LIST_NAME}
        fetch={{
          url: ['apiv2_example_list'],
          autoload: true
        }}

        primaryAction={(row) => ({
          type: LINK_BUTTON,
          label: trans('open', {}, 'actions'),
          target: `${props.path}/crud/${row.id}`
        })}
        actions={() => [
          {
            name: 'primary',
            type: CALLBACK_BUTTON,
            label: 'Primary action',
            callback: () => true,
            primary: true
          }, {
            name: 'other-1',
            type: CALLBACK_BUTTON,
            label: 'Other action 1',
            callback: () => true,
            group: 'Group 1'
          }, {
            name: 'other-2',
            type: CALLBACK_BUTTON,
            label: 'Other action 2',
            callback: () => true,
            group: 'Group 1'
          }, {
            name: 'other-3',
            type: CALLBACK_BUTTON,
            label: 'Other action 3',
            callback: () => true,
            group: 'Group 2'
          }, {
            name: 'disabled',
            type: CALLBACK_BUTTON,
            label: 'Disabled action',
            callback: () => true,
            disabled: true,
            group: 'Group 2'
          }, {
            name: 'dangerous',
            type: CALLBACK_BUTTON,
            label: 'Dangerous action',
            callback: () => true,
            dangerous: true
          }
        ]}
        definition={[
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            primary: true,
            displayed: true
          }, {
            name: 'meta.description',
            type: 'string',
            label: trans('description'),
            options: {long: true},
            displayed: true
          }, {
            name: 'meta.createdAt',
            alias: 'createdAt',
            type: 'date',
            label: trans('creation_date'),
            displayed: false
          }, {
            name: 'meta.updatedAt',
            alias: 'updatedAt',
            type: 'date',
            label: trans('modification_date'),
            displayed: true
          }, {
            name: 'meta.creator',
            type: 'user',
            label: trans('creator'),
            displayed: true
          }
        ]}

        card={CrudCard}
      />
    </ContentSizing>
  </ToolPage>

CrudList.propTypes = {
  path: T.string.isRequired
}

export {
  CrudList
}
