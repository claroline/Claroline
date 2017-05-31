import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

const PICKER_NAME = 'EXO_OBJECT_PICKER'
import {t} from '#/main/core/translation'

/* global Claroline */

class ResourcePicker extends Component {
  constructor(props){
    super(props)
    this.resourcePickerParams = {
      isPickerMultiSelectAllowed: this.props.multiple,
      callback: (nodes) => {
        this.addResource(nodes)
        // Remove checked nodes for next time
        nodes = {}
      }
    }

    if(this.props.typeWhiteList.length > 0){
      this.resourcePickerParams.typeWhiteList = this.props.typeWhiteList
    }

    if(this.props.typeBlackList.length > 0){
      this.resourcePickerParams.typeBlackList = this.props.typeBlackList
    }
  }

  addResource(nodes){
    if (typeof nodes === 'object') {
      for (const key in nodes) {
        if (nodes.hasOwnProperty(key)) {
          const object = {
            nodeId: key,
            node: nodes[key]
          }
          this.props.onSelect(object)
          break
        }
      }
    }
  }

  togglePicker(){
    if (!Claroline.ResourceManager.hasPicker(PICKER_NAME)) {
      Claroline.ResourceManager.createPicker(PICKER_NAME, this.resourcePickerParams, true)
    } else {
      // Open existing picker
      Claroline.ResourceManager.picker(PICKER_NAME, 'open')
    }
  }

  render(){
    return(
      <div>
        <a role="button" onClick={this.togglePicker.bind(this)}>
          <span className="fa fa-fw fa-folder-open"></span>&nbsp;{t('add_resource')}
        </a>
      </div>
    )
  }
}

ResourcePicker.propTypes = {
  onSelect: T.func.isRequired,
  multiple: T.bool,
  typeWhiteList: T.arrayOf(T.string),
  typeBlackList: T.arrayOf(T.string)
}

ResourcePicker.defaultProps = {
  multiple: false,
  typeWhiteList: [],
  typeBlackList: []
}

export {ResourcePicker}
