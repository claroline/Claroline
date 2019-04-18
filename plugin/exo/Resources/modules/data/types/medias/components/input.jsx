import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {selectors} from '#/plugin/exo/resources/quiz/editor/store'
import {MODAL_ADD_MEDIA} from '#/plugin/exo/data/types/medias/modals/editor'
import {getContentDefinition, isEditableType} from '#/plugin/exo/contents/utils'
import {ContentThumbnail} from '#/plugin/exo/contents/components/content-thumbnail'

class MediasInput extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentObjectId: null
    }
  }

  render() {
    return (
      <div>
        <div className="item-object-thumbnail-box">
          {this.props.value.map((object, index) =>
            <ContentThumbnail
              id={object.id}
              index={index}
              key={`item-object-${object.id}-thumbnail`}
              data={object.data || object.url}
              type={object.type}
              active={this.state.currentObjectId === object.id}
              canDelete={true}
              canEdit={isEditableType(object.type)}
              canSort={true}
              onSort={(source, destination) => {
                if (source !== destination) {
                  const newValue = cloneDeep(this.props.value)
                  const srcIndex = newValue.findIndex(o => o.id === source)
                  const sourceObject = newValue[srcIndex]
                  newValue.splice(srcIndex, 1)
                  const destIndex = newValue.findIndex(o => o.id === destination)
                  newValue.splice(destIndex, 0, sourceObject)
                  this.props.onChange(newValue)
                  this.setState({currentObjectId: null})
                }
              }}
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
          <ModalButton
            className="btn"
            title={trans('add_object', {}, 'quiz')}
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
          >
            <span className="fa fa-fw fa-plus"/>
            {trans('add_object', {}, 'quiz')}
          </ModalButton>
        </div>

        {this.state.currentObjectId && this.props.value.find(o => o.id === this.state.currentObjectId) &&
          React.createElement(
            getContentDefinition(this.props.value.find(o => o.id === this.state.currentObjectId).type).components.editor,
            {
              formName: selectors.FORM_NAME,
              path: `${this.props.path}.objects[${this.props.value.findIndex(o => o.id === this.state.currentObjectId)}]`
            }
          )
        }
      </div>
    )
  }
}

implementPropTypes(MediasInput, FormFieldTypes, {
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
