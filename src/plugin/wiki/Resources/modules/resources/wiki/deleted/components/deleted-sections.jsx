import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions} from '#/plugin/wiki/resources/wiki/deleted/store/actions'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

const DeletedSectionsComponent = props =>
  <section className="wiki-deleted-sections-list">
    <h2>{trans('deleted_sections', {}, 'icap_wiki')}</h2>
    <ListData
      name={selectors.STORE_NAME + '.deletedSections'}
      fetch={{
        url: ['apiv2_wiki_section_deleted_list', {wikiId: props.wiki.id}],
        autoload: true
      }}
      definition={[
        {
          name: 'activeContribution.title',
          alias: 'contribution',
          label: trans('title'),
          type: 'string',
          displayed: true,
          filterable: true
        }, {
          name: 'meta.createdAt',
          alias: 'creationDate',
          label: trans('creation_date', {}, 'platform'),
          type: 'date',
          displayed: true,
          filterable: true,
          options: {
            time: true
          }
        }, {
          name: 'meta.deletedAt',
          alias: 'deletionDate',
          label: trans('deleted', {}, 'icap_wiki'),
          type: 'date',
          displayed: true,
          filterable: true,
          options: {
            time: true
          }
        }, {
          name: 'meta.creator.name',
          alias: 'creator',
          label: trans('author', {}, 'platform'),
          type: 'string',
          displayed: true,
          render: rowData => rowData.meta.creator ? `${rowData.meta.creator.name}` : trans('unknown')
        }
      ]}
      actions={rows => [
        {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-undo',
          label: trans('restore_section', {}, 'icap_wiki'),
          callback: () => props.restoreSections(props.wiki.id, rows.map(item => item.id)),
          scope: ['object', 'collection'],
          displayed: true
        }, {
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-trash',
          dangerous: true,
          label: trans('remove_section', {}, 'icap_wiki'),
          scope: ['object', 'collection'],
          displayed: true,
          callback: () => props.removeSections(props.wiki.id, rows.map(item => item.id)),
          confirm: {
            message: trans('remove_confirmation', {}, 'icap_wiki'),
            button: trans('confirm')
          }
        }
      ]}
    />
  </section>

DeletedSectionsComponent.propTypes = {
  wiki: T.object.isRequired,
  restoreSections: T.func.isRequired,
  removeSections: T.func.isRequired
}

const DeletedSections = connect(
  (state) => ({
    wiki: selectors.wiki(state)
  }),
  (dispatch) => ({
    restoreSections: (wikiId, ids) => dispatch(actions.restoreSections(wikiId, ids)),
    removeSections: (wikiId, ids) => dispatch(actions.removeSections(wikiId, ids))
  })
)(DeletedSectionsComponent)

export {
  DeletedSections
}
