import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {TagCard} from '#/plugin/tag/card/components/tag'

const TagList = () =>
  <ListData
    name="tags"
    fetch={{
      url: ['apiv2_tag_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_tag_delete']
    }}
    primaryAction={(tag) => ({
      type: LINK_BUTTON,
      target: `/${tag.id}`
    })}
    definition={[
      {
        name: 'color',
        type: 'color',
        label: trans('color'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'name',
        type: 'string',
        label: trans('name'),
        primary: true,
        displayed: true
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        options: {
          long: true
        }
      }, {
        name: 'elements',
        type: 'number',
        label: trans('elements'),
        displayed: true
      }
    ]}
    card={TagCard}
  />

TagList.propTypes = {

}

export {
  TagList
}
