import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {ToolPage} from '#/main/core/tool'

import {selectors} from '#/plugin/tag/tools/tags/store'
import {TagCard} from '#/plugin/tag/card/components/tag'
import {TagIcon} from '#/plugin/tag/components/icon'

const TagList = (props) =>
  <ToolPage
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add-tag', {}, 'actions'),
        target: `${props.path}/new`,
        primary: true,
        displayed: props.canCreate
      }
    ]}
  >
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
          name: 'name',
          type: 'string',
          label: trans('tag', {}, 'tag'),
          primary: true,
          displayed: true,
          render: (tag) => (
            <div className="d-flex flex-direction-row gap-3 align-items-center">
              <TagIcon tag={tag} size="xs" />
              {tag.name}
            </div>
          )
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          displayed: true,
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
  </ToolPage>

TagList.propTypes = {
  path: T.string.isRequired,
  canCreate: T.bool.isRequired
}

export {
  TagList
}
