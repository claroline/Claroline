import React, {Component} from 'react'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {MODAL_ADD_MEDIA} from '#/plugin/exo/data/types/medias/modals/editor'
import {getContentDefinition, isEditableType} from '#/plugin/exo/contents/utils'
import {MediaThumbnail} from '#/plugin/exo/data/types/medias/components/thumbnail'

class MediasInput extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentObjectId: null
    }
  }

  render() {
    return (
      <>
        <div className="d-flex flex-row flex-wrap gap-2">
          {this.props.value.map((object) =>
            <MediaThumbnail
              id={object.id}
              key={`item-object-${object.id}-thumbnail`}
              data={object.data || object.url}
              type={object.type}
              active={this.state.currentObjectId === object.id}
              canDelete={true}
              canEdit={isEditableType(object.type)}
              handleEdit={e => {
                e.stopPropagation()
                this.setState({currentObjectId: this.state.currentObjectId === object.id ? null : object.id})
              }}
              handleDelete={e => {
                e.stopPropagation()
                const newValue = cloneDeep(this.props.value)
                const index = newValue.findIndex(o => o.id === object.id)

                if (-1 < index) {
                  newValue.splice(index, 1)
                  this.props.onChange(newValue)
                  this.setState({currentObjectId: null})
                }
              }}
            />
          )}
        </div>

        {this.state.currentObjectId && this.props.value.find(o => o.id === this.state.currentObjectId) &&
          React.createElement(
            getContentDefinition(this.props.value.find(o => o.id === this.state.currentObjectId).type).components.editor,
            {
              formName: selectors.FORM_NAME,
              path: `objects[${this.props.value.findIndex(o => o.id === this.state.currentObjectId)}]`
            }
          )
        }

        <Button
          className={classes('btn-add btn btn-link ms-n3', {
            'mt-3': !isEmpty(this.props.value)
          })}
          type={MODAL_BUTTON}
          icon="fa fa-fw fa-plus"
          label={trans('add_object', {}, 'quiz')}
          modal={[MODAL_ADD_MEDIA, {
            title: trans('add_object', {}, 'quiz'),
            handleSelect: (object) => {
              const newValue = cloneDeep(this.props.value)
              newValue.push(object)
              this.props.onChange(newValue)

              if (isEditableType(object.type)) {
                this.setState({currentObjectId: object.id})
              } else {
                this.setState({currentObjectId: null})
              }
            }
          }]}
        />
      </>
    )
  }
}

implementPropTypes(MediasInput, DataInputTypes, {
  value: T.arrayOf(T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    data: T.string,
    url: T.string
  })),
  path: T.string
}, {
  value: [],
  path: ''
})

export {
  MediasInput
}
