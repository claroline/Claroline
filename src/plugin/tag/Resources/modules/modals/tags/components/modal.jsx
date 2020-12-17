import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/tag/modals/tags/store'
import {Tag as TagTypes} from '#/plugin/tag/data/types/tag/prop-types'
import {TagCard} from '#/plugin/tag/card/components/tag'

const TagsModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'resetSelect', 'objectClass')}
      icon="fa fa-fw fa-tags"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: ['apiv2_tag_list'],
          autoload: true
        }}
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

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

TagsModal.propTypes = {
  objectClass: T.string, // TODO : filtering by objectClass doesn't work for now.
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(
    TagTypes.propTypes
  )).isRequired,
  resetSelect: T.func.isRequired
}

TagsModal.defaultProps = {
  title: trans('tags', {}, 'tag')
}

export {
  TagsModal
}
