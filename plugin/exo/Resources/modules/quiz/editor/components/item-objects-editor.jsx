import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {tex} from '#/main/core/translation'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {MODAL_ADD_CONTENT} from './../components/add-content-modal.jsx'
import {actions, OBJECT_CHANGE, OBJECT_MOVE, OBJECT_REMOVE} from './../actions.js'
import {getContentDefinition} from './../../../contents/content-types'
import {ContentThumbnail} from './../../../contents/components/content-thumbnail.jsx'

class ObjectsEditor extends Component {
  constructor(props) {
    super(props)
    this.state = {
      currentObjectId: ''
    }
  }

  selectObject(objectId) {
    const value = this.state.currentObjectId === objectId ? '' : objectId
    this.setState({
      currentObjectId: value
    })
  }

  hasErrors(objectId) {
    const object = this.props.item.objects.find(o => o.id === objectId)

    return object && object._errors && Object.keys(object._errors).length > 0
  }

  render() {
    return (
      <div>
        <div className="item-object-thumbnail-box">
          {this.props.item.objects.map((object, index) =>
            <ContentThumbnail
              id={object.id}
              index={index}
              key={`item-object-${object.id}-thumbnail`}
              data={object.data || object.url}
              type={object.type}
              active={this.state.currentObjectId === object.id}
              canDelete={true}
              canEdit={getContentDefinition(object.type).editable}
              canSort={true}
              validating={this.props.validating}
              hasErrors={this.hasErrors(object.id)}
              onSort={(source, destination) => {
                this.props.updateItemObjects(this.props.item.id, OBJECT_MOVE, {id: source, swapId: destination})
              }}
              handleEdit={e => {
                e.stopPropagation()
                this.selectObject(object.id)
              }}
              handleDelete={e => {
                e.stopPropagation()
                this.props.showModal(MODAL_DELETE_CONFIRM, {
                  title: tex('delete_object'),
                  question: tex('remove_object_confirm_message'),
                  handleConfirm: () => {
                    this.selectObject('')
                    this.props.updateItemObjects(this.props.item.id, OBJECT_REMOVE, {id: object.id})
                    this.props.updateItem(this.props.item.id, '_errors', {})
                  }
                })
              }}
            />
          )}
          <button
            type="button"
            className="btn btn-default"
            onClick={() =>
              this.props.showModal(MODAL_ADD_CONTENT, {
                title: tex('add_object'),
                handleSelect: (type) => {
                  this.props.closeModal()
                  const itemObject = this.props.createItemObject(this.props.item.id, type)
                  this.props.updateItem(this.props.item.id, '_errors', {})
                  const selectId = getContentDefinition(type).type === 'text' ? itemObject.id : ''
                  this.selectObject(selectId)
                  return itemObject
                },
                handleFileUpload: (objectId, file) => {
                  this.props.saveItemObjectFile(this.props.item.id, objectId, file)
                  return this.props.closeModal()
                }
              })
            }
          >
            <span className="fa fa-fw fa-plus"/>
            {tex('add_object')}
          </button>
        </div>
        {this.state.currentObjectId && this.props.item.objects.find(o => o.id === this.state.currentObjectId) &&
          React.createElement(
            getContentDefinition(this.props.item.objects.find(o => o.id === this.state.currentObjectId).type).editor.objectEditor,
            {
              object: this.props.item.objects.find(o => o.id === this.state.currentObjectId),
              validating: this.props.validating,
              onChange: content => {
                this.props.updateItemObjects(
                  this.props.item.id,
                  OBJECT_CHANGE,
                  {id: this.state.currentObjectId, property: 'data', value: content}
                )
                this.props.updateItem(this.props.item.id, '_errors', {})
              }
            }
          )
        }
      </div>
    )
  }
}

ObjectsEditor.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    objects: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      url: T.string,
      data: T.string,
      _errors: T.object
    })).isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  showModal: T.func.isRequired,
  closeModal: T.func.isRequired,

  updateItem: T.func.isRequired,
  createItemObject: T.func.isRequired,
  updateItemObjects: T.func.isRequired,
  saveItemObjectFile: T.func.isRequired
}

function mapStateToProps() {
  return {}
}

export default connect(mapStateToProps, actions)(ObjectsEditor)
