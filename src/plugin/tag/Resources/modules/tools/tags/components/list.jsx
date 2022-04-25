import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/tag/tools/tags/store'
import {TagCard} from '#/plugin/tag/card/components/tag'

const TagList = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.tags'}
    fetch={{
      url: ['apiv2_tag_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_tag_delete_bulk']
    }}
    primaryAction={(tag) => ({
      type: LINK_BUTTON,
      target: `${props.path}/${tag.id}`
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
        label: trans('elements', {}, 'tag'),
        displayed: true
      }
    ]}
    card={TagCard}
  />

TagList.propTypes = {
  path: T.string.isRequired
}

export {
  TagList
}
