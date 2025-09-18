import React from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {
  ListData,
  constants as listConst,
  actions as listActions
} from '#/main/app/content/list'
import {selectors as toolSelectors, ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'
import {DataMicro} from '#/main/app/data/components/micro'

import {selectors} from '#/plugin/tag/tools/tags/store'
import {TagCard} from '#/plugin/tag/card/components/tag'
import {MODAL_TAG} from '#/plugin/tag/tools/tags/modals/tag'

const TagList = () => {
  const listName = selectors.STORE_NAME + '.tags'
  const toolPath = useSelector(toolSelectors.path)
  const canCreate = useSelector((state) => hasPermission('create', toolSelectors.tool(state)))
  const canEdit = useSelector((state) => hasPermission('edit', toolSelectors.tool(state)))

  const dispatch = useDispatch()

  return (
    <ToolPage>
      <PageListSection
        title={trans('tags', {}, 'tag')}
        addAction={{
          name: 'add',
          type: MODAL_BUTTON,
          label: trans('add_tag', {}, 'actions'),
          modal: [MODAL_TAG, {
            onSave: () => dispatch(listActions.invalidateData(listName))
          }],
          displayed: canCreate
        }}
      >
        <ListData
          className="mb-5"
          flush={true}
          name={listName}
          fetch={{
            url: ['apiv2_tag_list'],
            autoload: true
          }}
          delete={{
            url: ['apiv2_tag_delete']
          }}
          primaryAction={(tag) => ({
            type: LINK_BUTTON,
            target: `${toolPath}/${tag.id}`
          })}
          definition={[
            {
              name: 'name',
              type: 'string',
              label: trans('tag', {}, 'tag'),
              primary: true,
              displayed: true,
              render: (tag) => <DataMicro object={tag} color={tag.color} />
            }, {
              name: 'id',
              type: 'string',
              label: trans('code'),
              displayable: true,
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
              displayed: true,
              sortable: false
            }
          ]}
          card={TagCard}
          actions={(rows) => [
            {
              name: 'edit',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              modal: [MODAL_TAG, {
                tag: rows[0],
                onSave: () => dispatch(listActions.invalidateData(listName))
              }],
              score: ['object'],
              group: trans('management'),
              displayed: canEdit,
              primary: true
            }
          ]}
          display={{
            current: listConst.DISPLAY_LIST
          }}
        />
      </PageListSection>
    </ToolPage>
  )
}

export {
  TagList
}
