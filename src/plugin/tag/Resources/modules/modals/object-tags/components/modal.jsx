import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans, transChoice} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {TagTypeahead} from '#/plugin/tag/components/typeahead'
import {Tag as TagTypes} from '#/plugin/tag/data/types/tag/prop-types'

const ObjectTagsModal = props =>
  <Modal
    {...omit(props, 'tags', 'objectClass', 'objects', 'update', 'loadTags', 'removeTag', 'addTag')}
    icon="fa fa-fw fa-tags"
    title={trans('tags', {}, 'tag')}
    subtitle={1 === props.objects.length ?
      props.objects[0].name
      :
      transChoice('count_elements', props.objects.length, {count: props.objects.length})
    }
    onEntering={() => props.loadTags(props.objectClass, props.objects)}
  >
    <div className="modal-body">
      <TagTypeahead
        canCreate={props.canCreate}
        select={(tagName) => props.addTag(props.objectClass, props.objects, {name: tagName}).then(() => {
          if (props.update) {
            props.update(props.objects)
          }
        })}
      />

      {0 === props.tags.length &&
        <div className="no-item-info">{trans('no_tag', {}, 'tag')}</div>
      }

      {0 !== props.tags.length &&
        <ul className="tags-list">
          {props.tags.map(tag =>
            <li key={tag.id} className="tag-item" style={{borderColor: tag.color}}>
              <span className="tag-color" style={{backgroundColor: tag.color}} />

              <div className="tag-meta">
                {tag.name}

                {tag.meta.description &&
                  <p className="tag-description">{tag.meta.description}</p>
                }
              </div>

              <Button
                className="tag-action btn btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-times"
                label={trans('delete', {}, 'actions')}
                tooltip="left"
                callback={() => props.removeTag(props.objectClass, props.objects, tag).then(() => {
                  if (props.update) {
                    props.update(props.objects)
                  }
                })}
              />
            </li>
          )}
        </ul>
      }
    </div>
  </Modal>

ObjectTagsModal.propTypes = {
  objectClass: T.string.isRequired,
  objects: T.arrayOf(T.shape({
    id: T.oneOfType([T.string, T.number]).isRequired,
    name: T.string.isRequired
  })),
  update: T.func,

  tags: T.arrayOf(T.shape(
    TagTypes.propTypes
  )),
  loadTags: T.func.isRequired,
  removeTag: T.func.isRequired,
  addTag: T.func.isRequired,
  canCreate: T.bool.isRequired
}

ObjectTagsModal.defaultProps = {
  tags: []
}

export {
  ObjectTagsModal
}
