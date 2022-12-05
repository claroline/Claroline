import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/components/data'

class SectionDeleteModal extends Component {
  constructor(props) {
    super(props)
    
    this.state = {
      data: {children: false}
    }
    this.updateProp = this.updateProp.bind(this)
    this.save = this.save.bind(this)
  }
  
  updateProp(propName, propValue) {
    const newData = Object.assign({}, this.state.data)
    newData[propName] = propValue
    
    this.setState({
      data: newData
    })
  }
  
  save() {
    this.props.deleteSection(this.state.data.children)
    this.props.fadeModal()
    this.setState({
      data: {children: false}
    })
  }
  
  render() {
    return (
      <Modal
        {...omit(this.props, 'deleteSection', 'sectionTitle')}
        icon="fa fa-fw fa-trash"
        title={trans('delete_confirmation', {'sectionTitle': this.props.sectionTitle}, 'icap_wiki')}
      >
        <FormData
          level={5}
          data={this.state.data}
          setErrors={() => {}}
          updateProp={this.updateProp}
          sections={[
            {
              id: 'general',
              title: '',
              primary: true,
              fields: [
                {
                  name: 'children',
                  type: 'boolean',
                  label: trans('icap_wiki_delete_section_type_children', {}, 'icap_wiki')
                }
              ]
            }
          ]}
        />
        <button
          className="modal-btn btn btn-danger"
          onClick={this.save}
        >
          {trans('confirm')}
        </button>
      </Modal>
    )}
}
  
SectionDeleteModal.propTypes = {
  'deleteSection': T.func.isRequired,
  'sectionTitle': T.string.isRequired,
  'fadeModal': T.func.isRequired
}

export {
  SectionDeleteModal
}